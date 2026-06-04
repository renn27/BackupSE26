from __future__ import annotations

import html
import shutil
import struct
import zipfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs" / "Panduan_Asisten_SE2026.pptx"
SCREENSHOT_DIR = ROOT / "docs" / "screenshots"
WORK = ROOT / "docs" / "_pptx_work"

SLIDE_W = 13_333_333
SLIDE_H = 7_500_000


def esc(value: str) -> str:
    return html.escape(value, quote=True)


def png_size(path: Path) -> tuple[int, int]:
    with path.open("rb") as f:
        f.seek(16)
        return struct.unpack(">II", f.read(8))


def emu(inches: float) -> int:
    return int(inches * 914400)


def color(name: str) -> str:
    palette = {
        "ink": "1F2937",
        "muted": "64748B",
        "orange": "F68B24",
        "rust": "B45309",
        "gold": "FBBF24",
        "soft": "F8FAFC",
        "line": "E2E8F0",
        "green": "22C55E",
        "blue": "2563EB",
        "rose": "F43F5E",
        "white": "FFFFFF",
    }
    return palette[name]


def tx_box(
    shape_id: int,
    x: int,
    y: int,
    w: int,
    h: int,
    text: str,
    size: int = 2400,
    bold: bool = False,
    fill: str | None = None,
    line: str | None = None,
    font_color: str = "ink",
    radius: bool = False,
    align: str = "l",
) -> str:
    bold_xml = '<a:b/>' if bold else ""
    fill_xml = (
        f'<a:solidFill><a:srgbClr val="{color(fill)}"/></a:solidFill>'
        if fill
        else '<a:noFill/>'
    )
    line_xml = (
        f'<a:ln w="9525"><a:solidFill><a:srgbClr val="{color(line)}"/></a:solidFill></a:ln>'
        if line
        else '<a:ln><a:noFill/></a:ln>'
    )
    preset = "roundRect" if radius else "rect"
    paragraphs = []
    for raw_line in text.split("\n"):
        if raw_line.startswith("- "):
            bullet_text = raw_line[2:]
            paragraphs.append(
                f'<a:p><a:pPr marL="285750" indent="-171450"><a:buChar char="•"/></a:pPr>'
                f'<a:r><a:rPr lang="id-ID" sz="{size}"><a:solidFill><a:srgbClr val="{color(font_color)}"/></a:solidFill>{bold_xml}</a:rPr>'
                f'<a:t>{esc(bullet_text)}</a:t></a:r></a:p>'
            )
        elif raw_line == "":
            paragraphs.append("<a:p/>")
        else:
            paragraphs.append(
                f'<a:p><a:pPr algn="{align}"/>'
                f'<a:r><a:rPr lang="id-ID" sz="{size}"><a:solidFill><a:srgbClr val="{color(font_color)}"/></a:solidFill>{bold_xml}</a:rPr>'
                f'<a:t>{esc(raw_line)}</a:t></a:r></a:p>'
            )

    return f"""
    <p:sp>
      <p:nvSpPr><p:cNvPr id="{shape_id}" name="Text {shape_id}"/><p:cNvSpPr txBox="1"/><p:nvPr/></p:nvSpPr>
      <p:spPr>
        <a:xfrm><a:off x="{x}" y="{y}"/><a:ext cx="{w}" cy="{h}"/></a:xfrm>
        <a:prstGeom prst="{preset}"><a:avLst/></a:prstGeom>
        {fill_xml}
        {line_xml}
      </p:spPr>
      <p:txBody>
        <a:bodyPr wrap="square" lIns="137160" tIns="91440" rIns="137160" bIns="91440"/>
        <a:lstStyle/>
        {''.join(paragraphs)}
      </p:txBody>
    </p:sp>
    """


def picture(shape_id: int, rel_id: str, x: int, y: int, w: int, h: int) -> str:
    return f"""
    <p:pic>
      <p:nvPicPr><p:cNvPr id="{shape_id}" name="Screenshot {shape_id}"/><p:cNvPicPr/><p:nvPr/></p:nvPicPr>
      <p:blipFill><a:blip r:embed="{rel_id}"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>
      <p:spPr><a:xfrm><a:off x="{x}" y="{y}"/><a:ext cx="{w}" cy="{h}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:ln w="12700"><a:solidFill><a:srgbClr val="{color('line')}"/></a:solidFill></a:ln></p:spPr>
    </p:pic>
    """


def fit_image(path: Path, x: int, y: int, max_w: int, max_h: int) -> tuple[int, int, int, int]:
    iw, ih = png_size(path)
    scale = min(max_w / iw, max_h / ih)
    w = int(iw * scale)
    h = int(ih * scale)
    return x + (max_w - w) // 2, y + (max_h - h) // 2, w, h


def slide_xml(title: str, body: str, image: str | None = None, accent: str = "orange") -> tuple[str, str]:
    shapes = [
        tx_box(2, emu(0.45), emu(0.28), emu(12.4), emu(0.48), "ASISTEN SE2026", 1500, True, font_color="rust"),
        tx_box(3, emu(0.45), emu(0.72), emu(8.0), emu(0.75), title, 3200, True),
        tx_box(4, emu(0.45), emu(1.52), emu(4.35 if image else 12.3), emu(5.35), body, 1800, False),
        tx_box(5, emu(0.45), emu(6.98), emu(12.4), emu(0.18), "", 1000, fill=accent, line=accent),
    ]
    rels = ""
    if image:
        path = SCREENSHOT_DIR / image
        ix, iy, iw, ih = fit_image(path, emu(4.95), emu(1.55), emu(7.75), emu(5.05))
        shapes.append(picture(6, "rId1", ix, iy, iw, ih))
        rels = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/{image}"/>
</Relationships>"""
    else:
        rels = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>"""

    xml = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
  <p:cSld>
    <p:bg><p:bgPr><a:solidFill><a:srgbClr val="{color('soft')}"/></a:solidFill><a:effectLst/></p:bgPr></p:bg>
    <p:spTree>
      <p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>
      <p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>
      {''.join(shapes)}
    </p:spTree>
  </p:cSld>
  <p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>
</p:sld>"""
    return xml, rels


SLIDES = [
    ("Panduan Penggunaan Sistem", "Nama aplikasi:\nASISTEN SE2026\n\nAplikasi Simpan, Informasi Progres, dan Tanya Konsepnya Sensus Ekonomi 2026.\n\nDokumen ini memandu pengguna untuk login, mengenali menu, dan menjalankan fitur utama pada role Petugas dan Superadmin.", None),
    ("Tujuan dan Link", "Tujuan:\n- Membantu petugas menyimpan foto laporan dan file backup ke Google Drive pribadi.\n- Memantau progres pendataan Monitoring SBR.\n- Menyediakan akses Tanya KonDef sebagai referensi konsep dan definisi.\n- Membantu admin mengelola user, file, assignment wilayah, dan rekap progres.\n\nLink:\n- URL aplikasi: http://localhost\n- URL lokal saat dokumentasi dibuat: http://127.0.0.1:8000", None),
    ("Tahapan Login", "Tahapan login:\n- Buka link aplikasi melalui browser.\n- Klik tombol Masuk dengan Google.\n- Pilih akun Google yang terdaftar pada sistem.\n- Berikan izin akses yang diminta.\n- Setelah berhasil, sistem mengarahkan pengguna ke dashboard sesuai role.\n\nCatatan:\nAkun harus aktif dan memiliki sesi Google yang valid.", "01-login.png"),
    ("Menu yang Tersedia", "Role Petugas:\n- Backup\n- Monitoring SBR\n- Tanya KonDef\n\nRole Superadmin:\n- Dashboard\n- Manajemen User\n- Semua File\n- Monitoring SBR\n\nMenu yang tampil mengikuti role akun setelah login.", None),
    ("Petugas - Backup", "Cara penggunaan:\n- Buka menu Backup.\n- Gunakan area Upload Foto Laporan untuk mengunggah JPG, PNG, WebP, atau HEIC.\n- Gunakan area Upload Data Backup untuk mengunggah file backup.\n- Isi nama file baru bila ingin mengganti nama sebelum upload.\n- Pantau daftar File Saya untuk melihat, mencari, mengunduh, membuka Drive, atau menghapus file.", "06-petugas-backup.png"),
    ("Petugas - Monitoring SBR", "Cara penggunaan:\n- Buka menu Monitoring SBR.\n- Pastikan wilayah tugas sudah tampil di bagian Wilayah Tugas.\n- Cari usaha berdasarkan ID SBR atau nama usaha.\n- Lihat status usaha pada tabel.\n- Perbarui status usaha sesuai kondisi lapangan: Aktif, Tidak Aktif, Pindah, atau Tidak Ditemukan.\n- Tambahkan catatan bila diperlukan.", "07-petugas-monitoring-sbr.png"),
    ("Petugas - Tanya KonDef", "Cara penggunaan:\n- Buka menu Tanya KonDef.\n- Gunakan halaman ini sebagai akses bantuan konsep dan definisi Sensus Ekonomi 2026.\n- Pilih atau buka materi yang tersedia sesuai kebutuhan lapangan.\n- Gunakan menu ini ketika perlu memastikan istilah, konsep, atau definisi sebelum melakukan pencatatan.", "08-petugas-tanya-kondef.png"),
    ("Superadmin - Dashboard", "Cara penggunaan:\n- Buka menu Dashboard.\n- Pantau total petugas aktif, total foto, total backup, dan storage digunakan.\n- Lihat grafik aktivitas upload 7 hari terakhir.\n- Periksa aktivitas terkini dan file terbaru yang diunggah.\n- Gunakan informasi ini untuk memantau kondisi umum sistem.", "02-admin-dashboard.png"),
    ("Superadmin - Manajemen User", "Cara penggunaan:\n- Buka menu Manajemen User.\n- Lihat daftar user, role, status, dan waktu login terakhir.\n- Aktifkan atau nonaktifkan akun petugas sesuai kebutuhan.\n- Hapus akun bila memang tidak digunakan lagi.\n- Pastikan hanya petugas berwenang yang tetap aktif.", "03-admin-users.png"),
    ("Superadmin - Semua File", "Cara penggunaan:\n- Buka menu Semua File.\n- Filter file berdasarkan petugas, jenis file, status, atau kata kunci pencarian.\n- Lihat daftar file yang tersimpan dari semua petugas.\n- Gunakan aksi lihat, download, atau export untuk kebutuhan monitoring dan rekap.", "04-admin-files.png"),
    ("Superadmin - Monitoring SBR", "Cara penggunaan:\n- Buka menu Monitoring SBR.\n- Pantau ringkasan total usaha, desa, desa ditugaskan, progress, dan komposisi status.\n- Upload data usaha SBR melalui file Excel bila diperlukan.\n- Kelola assignment desa kepada petugas.\n- Lihat monitoring per wilayah dan per petugas untuk evaluasi progres.", "05-admin-monitoring-sbr.png"),
    ("Alur Kerja Singkat", "Petugas:\n- Login dengan Google.\n- Upload foto laporan atau backup pada menu Backup.\n- Cek dan perbarui status usaha pada Monitoring SBR.\n- Gunakan Tanya KonDef saat butuh rujukan konsep.\n\nSuperadmin:\n- Login dengan Google.\n- Pantau dashboard dan file masuk.\n- Kelola user aktif.\n- Upload data SBR dan atur assignment wilayah.\n- Evaluasi progres pencatatan dari dashboard Monitoring SBR.", None),
    ("Penutup", "ASISTEN SE2026 membantu proses backup dokumen, monitoring progres SBR, dan akses referensi konsep dalam satu aplikasi.\n\nPastikan:\n- Login menggunakan akun yang terdaftar.\n- File yang diunggah sesuai kebutuhan kegiatan.\n- Status usaha diperbarui berdasarkan kondisi lapangan.\n- Assignment wilayah dikelola dengan benar oleh admin.\n\nTerima kasih.", None),
]


def write(path: Path, content: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content, encoding="utf-8")


def build() -> None:
    if WORK.exists():
        shutil.rmtree(WORK)

    (WORK / "_rels").mkdir(parents=True)
    (WORK / "ppt" / "_rels").mkdir(parents=True)
    (WORK / "ppt" / "slides" / "_rels").mkdir(parents=True)
    (WORK / "ppt" / "media").mkdir(parents=True)
    (WORK / "ppt" / "theme").mkdir(parents=True)
    (WORK / "docProps").mkdir(parents=True)

    used_images = {s[2] for s in SLIDES if s[2]}
    for image in used_images:
        shutil.copy2(SCREENSHOT_DIR / image, WORK / "ppt" / "media" / image)

    defaults = """
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Default Extension="png" ContentType="image/png"/>
"""
    overrides = [
        '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>',
        '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>',
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>',
        '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>',
    ]
    for index in range(1, len(SLIDES) + 1):
        overrides.append(f'<Override PartName="/ppt/slides/slide{index}.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>')
    write(WORK / "[Content_Types].xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">{defaults}{''.join(overrides)}</Types>""")

    write(WORK / "_rels" / ".rels", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>""")

    slide_ids = []
    pres_rels = ['<Relationship Id="rIdTheme" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>']
    for index, slide in enumerate(SLIDES, start=1):
        slide_ids.append(f'<p:sldId id="{255 + index}" r:id="rId{index}"/>')
        pres_rels.append(f'<Relationship Id="rId{index}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide{index}.xml"/>')
        xml, rels = slide_xml(*slide)
        write(WORK / "ppt" / "slides" / f"slide{index}.xml", xml)
        write(WORK / "ppt" / "slides" / "_rels" / f"slide{index}.xml.rels", rels)

    write(WORK / "ppt" / "presentation.xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
  <p:sldIdLst>{''.join(slide_ids)}</p:sldIdLst>
  <p:sldSz cx="{SLIDE_W}" cy="{SLIDE_H}" type="wide"/>
  <p:notesSz cx="6858000" cy="9144000"/>
</p:presentation>""")

    write(WORK / "ppt" / "_rels" / "presentation.xml.rels", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">{''.join(pres_rels)}</Relationships>""")

    write(WORK / "ppt" / "theme" / "theme1.xml", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Asisten SE2026">
  <a:themeElements>
    <a:clrScheme name="Asisten"><a:dk1><a:srgbClr val="1F2937"/></a:dk1><a:lt1><a:srgbClr val="FFFFFF"/></a:lt1><a:dk2><a:srgbClr val="334155"/></a:dk2><a:lt2><a:srgbClr val="F8FAFC"/></a:lt2><a:accent1><a:srgbClr val="F68B24"/></a:accent1><a:accent2><a:srgbClr val="2563EB"/></a:accent2><a:accent3><a:srgbClr val="22C55E"/></a:accent3><a:accent4><a:srgbClr val="FBBF24"/></a:accent4><a:accent5><a:srgbClr val="F43F5E"/></a:accent5><a:accent6><a:srgbClr val="64748B"/></a:accent6><a:hlink><a:srgbClr val="2563EB"/></a:hlink><a:folHlink><a:srgbClr val="7C3AED"/></a:folHlink></a:clrScheme>
    <a:fontScheme name="Office"><a:majorFont><a:latin typeface="Aptos Display"/></a:majorFont><a:minorFont><a:latin typeface="Aptos"/></a:minorFont></a:fontScheme>
    <a:fmtScheme name="Office"><a:fillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:fillStyleLst><a:lnStyleLst><a:ln w="9525"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:ln></a:lnStyleLst><a:effectStyleLst><a:effectStyle><a:effectLst/></a:effectStyle></a:effectStyleLst><a:bgFillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:bgFillStyleLst></a:fmtScheme>
  </a:themeElements>
</a:theme>""")

    write(WORK / "docProps" / "core.xml", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>Panduan ASISTEN SE2026</dc:title><dc:creator>Codex</dc:creator><cp:lastModifiedBy>Codex</cp:lastModifiedBy></cp:coreProperties>""")
    write(WORK / "docProps" / "app.xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Codex</Application><Slides>{len(SLIDES)}</Slides></Properties>""")

    if OUT.exists():
        OUT.unlink()
    with zipfile.ZipFile(OUT, "w", zipfile.ZIP_DEFLATED) as zf:
        for file in WORK.rglob("*"):
            if file.is_file():
                zf.write(file, file.relative_to(WORK).as_posix())


if __name__ == "__main__":
    build()
    print(OUT)
