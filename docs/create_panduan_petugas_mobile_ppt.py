from __future__ import annotations

import html
import shutil
import struct
import zipfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SCREENSHOT_DIR = ROOT / "docs" / "mobile_screenshots"
OUT = ROOT / "docs" / "Panduan_Petugas_Mobile_Asisten_SE2026.pptx"
WORK = ROOT / "docs" / "_mobile_pptx_work"


def emu(inches: float) -> int:
    return int(inches * 914400)


SLIDE_W = emu(7.5)
SLIDE_H = emu(13.333)


PALETTE = {
    "ink": "1F2937",
    "muted": "64748B",
    "orange": "F68B24",
    "rust": "B45309",
    "soft": "F8FAFC",
    "line": "E2E8F0",
    "white": "FFFFFF",
    "green": "16A34A",
    "blue": "2563EB",
}


def c(name: str) -> str:
    return PALETTE[name]


def esc(text: str) -> str:
    return html.escape(text, quote=True)


def png_size(path: Path) -> tuple[int, int]:
    with path.open("rb") as fh:
        fh.seek(16)
        return struct.unpack(">II", fh.read(8))


def fit(path: Path, x: int, y: int, max_w: int, max_h: int) -> tuple[int, int, int, int]:
    iw, ih = png_size(path)
    scale = min(max_w / iw, max_h / ih)
    w = int(iw * scale)
    h = int(ih * scale)
    return x + (max_w - w) // 2, y + (max_h - h) // 2, w, h


def text_box(
    shape_id: int,
    x: int,
    y: int,
    w: int,
    h: int,
    text: str,
    size: int = 1450,
    bold: bool = False,
    fill: str | None = None,
    line: str | None = None,
    font_color: str = "ink",
    radius: bool = False,
) -> str:
    fill_xml = f'<a:solidFill><a:srgbClr val="{c(fill)}"/></a:solidFill>' if fill else '<a:noFill/>'
    line_xml = f'<a:ln w="9525"><a:solidFill><a:srgbClr val="{c(line)}"/></a:solidFill></a:ln>' if line else '<a:ln><a:noFill/></a:ln>'
    bold_xml = "<a:b/>" if bold else ""
    preset = "roundRect" if radius else "rect"
    paragraphs = []
    for raw in text.split("\n"):
        if raw.startswith("- "):
            item = raw[2:]
            paragraphs.append(
                f'<a:p><a:pPr marL="228600" indent="-137160"><a:buChar char="•"/></a:pPr>'
                f'<a:r><a:rPr lang="id-ID" sz="{size}"><a:solidFill><a:srgbClr val="{c(font_color)}"/></a:solidFill>{bold_xml}</a:rPr>'
                f'<a:t>{esc(item)}</a:t></a:r></a:p>'
            )
        elif raw == "":
            paragraphs.append("<a:p/>")
        else:
            paragraphs.append(
                f'<a:p><a:r><a:rPr lang="id-ID" sz="{size}"><a:solidFill><a:srgbClr val="{c(font_color)}"/></a:solidFill>{bold_xml}</a:rPr>'
                f'<a:t>{esc(raw)}</a:t></a:r></a:p>'
            )
    return f"""
    <p:sp>
      <p:nvSpPr><p:cNvPr id="{shape_id}" name="Text {shape_id}"/><p:cNvSpPr txBox="1"/><p:nvPr/></p:nvSpPr>
      <p:spPr><a:xfrm><a:off x="{x}" y="{y}"/><a:ext cx="{w}" cy="{h}"/></a:xfrm><a:prstGeom prst="{preset}"><a:avLst/></a:prstGeom>{fill_xml}{line_xml}</p:spPr>
      <p:txBody><a:bodyPr wrap="square" lIns="118000" tIns="65000" rIns="118000" bIns="65000"/><a:lstStyle/>{''.join(paragraphs)}</p:txBody>
    </p:sp>
    """


def picture(shape_id: int, rel_id: str, x: int, y: int, w: int, h: int) -> str:
    return f"""
    <p:pic>
      <p:nvPicPr><p:cNvPr id="{shape_id}" name="Mobile Screenshot {shape_id}"/><p:cNvPicPr/><p:nvPr/></p:nvPicPr>
      <p:blipFill><a:blip r:embed="{rel_id}"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>
      <p:spPr><a:xfrm><a:off x="{x}" y="{y}"/><a:ext cx="{w}" cy="{h}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:ln w="12700"><a:solidFill><a:srgbClr val="{c('line')}"/></a:solidFill></a:ln></p:spPr>
    </p:pic>
    """


def slide_xml(title: str, body: str, image: str | None = None) -> tuple[str, str]:
    shapes = [
        text_box(2, emu(0.35), emu(0.22), emu(6.8), emu(0.35), "PANDUAN MOBILE PETUGAS", 1050, True, font_color="rust"),
        text_box(3, emu(0.35), emu(0.60), emu(6.8), emu(0.58), title, 2350, True),
    ]
    if image:
        shapes.append(text_box(4, emu(0.35), emu(1.23), emu(6.8), emu(1.72), body, 1250))
        path = SCREENSHOT_DIR / image
        ix, iy, iw, ih = fit(path, emu(1.18), emu(3.0), emu(5.15), emu(9.95))
        shapes.append(picture(5, "rId1", ix, iy, iw, ih))
        rels = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/{image}"/>
</Relationships>"""
    else:
        shapes.append(text_box(4, emu(0.48), emu(1.55), emu(6.54), emu(9.8), body, 1700, False, fill="white", line="line", radius=True))
        rels = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>"""
    shapes.append(text_box(6, emu(0.35), emu(12.96), emu(6.8), emu(0.12), "", 800, fill="orange", line="orange"))

    xml = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
  <p:cSld>
    <p:bg><p:bgPr><a:solidFill><a:srgbClr val="{c('soft')}"/></a:solidFill><a:effectLst/></p:bgPr></p:bg>
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
    ("ASISTEN SE2026", "Panduan mobile khusus Petugas\n\nIsi panduan:\n- Login aplikasi\n- Navigasi mobile\n- Backup foto laporan\n- Backup file data\n- File Saya, pencarian, filter, dan aksi file\n- Monitoring SBR dan update status usaha\n- Tanya KonDef\n\nDibuat berdasarkan tampilan mobile aplikasi.", None),
    ("Tujuan Petugas", "Tujuan penggunaan aplikasi bagi Petugas:\n- Menyimpan foto laporan ke Google Drive pribadi.\n- Menyimpan file backup kegiatan SE2026.\n- Melihat file yang sudah diunggah.\n- Memantau daftar usaha SBR sesuai wilayah tugas.\n- Memperbarui status usaha berdasarkan kondisi lapangan.\n- Membuka Tanya KonDef untuk rujukan konsep dan definisi.", None),
    ("Login Mobile", "Langkah login:\n- Buka link aplikasi di browser HP.\n- Tekan Masuk dengan Google.\n- Pilih akun Google yang terdaftar.\n- Berikan izin akses jika diminta.\n- Setelah berhasil, pengguna masuk ke menu Backup.", "01-mobile-login.png"),
    ("Navigasi Mobile", "Di HP tersedia dua navigasi:\n- Tombol menu di kiri atas untuk membuka sidebar.\n- Navigasi bawah untuk pindah cepat ke Backup, SBR, dan Tanya.\n- Menu aktif ditandai warna oranye.\n- Gunakan navigasi bawah saat bekerja cepat di lapangan.", "02-mobile-backup-upload.png"),
    ("Ringkasan Backup", "Pada menu Backup, bagian atas menampilkan:\n- Nama petugas yang sedang login.\n- Total file yang sudah diunggah.\n- Pemakaian storage dibanding batas 15 GB.\n- Dua area upload utama: Foto Laporan dan Data Backup.", "02a-mobile-backup-ringkasan.png"),
    ("Upload Foto Laporan", "Cara upload foto:\n- Tekan area Upload Foto Laporan.\n- Pilih foto dari galeri/kamera perangkat.\n- Format yang didukung: JPG, PNG, WebP, HEIC.\n- Ukuran maksimal 10 MB.\n- Isi nama baru bila perlu, lalu tekan Unggah Foto.\n- Pantau progress sampai berhasil.", "02b-mobile-backup-upload-area.png"),
    ("Upload Data Backup", "Cara upload data backup:\n- Tekan area Upload Data Backup.\n- Pilih file backup dari perangkat.\n- Semua ekstensi file dapat dipilih.\n- Ukuran maksimal 50 MB.\n- Isi nama baru bila ingin merapikan nama file.\n- Tekan Unggah Backup dan tunggu proses selesai.", "05a-mobile-filter-backup-empty.png"),
    ("File Saya", "Bagian File Saya digunakan untuk:\n- Melihat semua file milik petugas.\n- Mencari file berdasarkan nama.\n- Melihat ukuran dan tanggal upload.\n- Mengakses tombol aksi di sisi kanan atau menu titik tiga pada mode grid.\n- Berpindah antara mode tabel dan grid.", "03a-mobile-file-saya-list.png"),
    ("Filter Foto", "Filter Foto Laporan menampilkan hanya file gambar:\n- Tekan tab/icon Foto Laporan.\n- Tampilan grid memudahkan cek thumbnail foto.\n- Gunakan pencarian bila nama file banyak.\n- Menu titik tiga pada kartu file dipakai untuk aksi lanjutan.", "04a-mobile-filter-foto-grid.png"),
    ("Filter Backup", "Filter Data Backup menampilkan hanya file backup:\n- Tekan tab/icon Data Backup.\n- Jika belum ada file backup, sistem menampilkan status kosong.\n- Setelah upload backup berhasil, file muncul pada daftar ini.\n- Gunakan filter ini untuk memisahkan foto dan dokumen backup.", "05a-mobile-filter-backup-empty.png"),
    ("Aksi File", "Aksi yang tersedia pada file:\n- Buka Drive untuk melihat file di Google Drive jika link tersedia.\n- Download untuk mengunduh file kembali.\n- Hapus untuk menghapus file dari daftar dan Drive.\n- Gunakan aksi hapus dengan hati-hati karena file dapat hilang permanen.", "04a-mobile-filter-foto-grid.png"),
    ("Monitoring SBR", "Menu SBR digunakan untuk pekerjaan monitoring usaha:\n- Menampilkan nama petugas.\n- Menampilkan wilayah tugas yang sudah ditetapkan admin.\n- Menyediakan kolom pencarian ID SBR atau nama usaha.\n- Menampilkan tabel daftar usaha sesuai wilayah tugas.", "06a-mobile-sbr-daftar-usaha.png"),
    ("Daftar Usaha", "Pada tabel usaha, petugas dapat melihat:\n- Nomor urut.\n- ID SBR.\n- Nama usaha.\n- Status pencatatan.\n- Desa, kecamatan, dan alamat.\n\nPada layar HP, tabel dapat digeser horizontal untuk melihat kolom kanan.", "06b-mobile-sbr-table-detail.png"),
    ("Pencarian Usaha", "Cara mencari usaha:\n- Buka menu SBR.\n- Ketik ID SBR atau nama usaha pada kolom pencarian.\n- Sistem menampilkan hasil yang cocok.\n- Gunakan pencarian saat daftar usaha panjang atau saat mencari usaha tertentu di lapangan.", "07a-mobile-sbr-search-results.png"),
    ("Update Status SBR", "Cara memperbarui status:\n- Temukan usaha melalui daftar atau pencarian.\n- Buka kontrol status pada baris usaha.\n- Pilih status sesuai kondisi: Aktif, Tidak Aktif, Pindah, atau Tidak Ditemukan.\n- Isi catatan bila diperlukan.\n- Simpan, lalu pastikan label status berubah.", "06b-mobile-sbr-table-detail.png"),
    ("Tanya KonDef", "Menu Tanya KonDef digunakan untuk referensi cepat:\n- Buka menu Tanya pada navigasi bawah.\n- Tekan Buka Sekarang.\n- Sistem membuka notebook referensi di tab baru.\n- Gunakan saat perlu memastikan konsep, definisi, atau perlakuan pencatatan.", "08-mobile-tanya-kondef.png"),
    ("Tips Lapangan", "Tips penggunaan mobile:\n- Pastikan akun Google yang dipakai adalah akun terdaftar.\n- Gunakan koneksi stabil saat upload file besar.\n- Beri nama file yang mudah dikenali.\n- Cek kembali filter Foto dan Backup setelah upload.\n- Gunakan pencarian SBR sebelum mengubah status.\n- Periksa status usaha setelah menyimpan perubahan.", None),
    ("Penutup", "Panduan ini berfokus pada pekerjaan Petugas melalui tampilan mobile.\n\nRingkasnya:\n- Login dengan Google.\n- Upload foto dan file backup melalui menu Backup.\n- Kelola file pada File Saya.\n- Cari dan update status usaha melalui menu SBR.\n- Gunakan Tanya KonDef untuk rujukan konsep.\n\nTerima kasih.", None),
]


def write(path: Path, content: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content, encoding="utf-8")


def build() -> None:
    if WORK.exists():
        shutil.rmtree(WORK)
    for path in [
        WORK / "_rels",
        WORK / "ppt" / "_rels",
        WORK / "ppt" / "slides" / "_rels",
        WORK / "ppt" / "theme",
        WORK / "ppt" / "media",
        WORK / "docProps",
    ]:
        path.mkdir(parents=True, exist_ok=True)

    images = {slide[2] for slide in SLIDES if slide[2]}
    for image in images:
        shutil.copy2(SCREENSHOT_DIR / image, WORK / "ppt" / "media" / image)

    overrides = [
        '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>',
        '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>',
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>',
        '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>',
    ]
    for index in range(1, len(SLIDES) + 1):
        overrides.append(f'<Override PartName="/ppt/slides/slide{index}.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>')
    write(WORK / "[Content_Types].xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Default Extension="png" ContentType="image/png"/>
{''.join(overrides)}
</Types>""")
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
  <p:sldSz cx="{SLIDE_W}" cy="{SLIDE_H}" type="custom"/>
  <p:notesSz cx="6858000" cy="9144000"/>
</p:presentation>""")
    write(WORK / "ppt" / "_rels" / "presentation.xml.rels", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">{''.join(pres_rels)}</Relationships>""")
    write(WORK / "ppt" / "theme" / "theme1.xml", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Asisten Mobile"><a:themeElements><a:clrScheme name="Asisten"><a:dk1><a:srgbClr val="1F2937"/></a:dk1><a:lt1><a:srgbClr val="FFFFFF"/></a:lt1><a:dk2><a:srgbClr val="334155"/></a:dk2><a:lt2><a:srgbClr val="F8FAFC"/></a:lt2><a:accent1><a:srgbClr val="F68B24"/></a:accent1><a:accent2><a:srgbClr val="2563EB"/></a:accent2><a:accent3><a:srgbClr val="16A34A"/></a:accent3><a:accent4><a:srgbClr val="FBBF24"/></a:accent4><a:accent5><a:srgbClr val="F43F5E"/></a:accent5><a:accent6><a:srgbClr val="64748B"/></a:accent6><a:hlink><a:srgbClr val="2563EB"/></a:hlink><a:folHlink><a:srgbClr val="7C3AED"/></a:folHlink></a:clrScheme><a:fontScheme name="Office"><a:majorFont><a:latin typeface="Aptos Display"/></a:majorFont><a:minorFont><a:latin typeface="Aptos"/></a:minorFont></a:fontScheme><a:fmtScheme name="Office"><a:fillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:fillStyleLst><a:lnStyleLst><a:ln w="9525"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:ln></a:lnStyleLst><a:effectStyleLst><a:effectStyle><a:effectLst/></a:effectStyle></a:effectStyleLst><a:bgFillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:bgFillStyleLst></a:fmtScheme></a:themeElements></a:theme>""")
    write(WORK / "docProps" / "core.xml", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Panduan Mobile Petugas ASISTEN SE2026</dc:title><dc:creator>Codex</dc:creator><cp:lastModifiedBy>Codex</cp:lastModifiedBy></cp:coreProperties>""")
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
