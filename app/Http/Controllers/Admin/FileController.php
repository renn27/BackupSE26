<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = File::with('user');
        $selectedUser = null;

        if ($request->search) {
            $query->where('original_name', 'like', "%{$request->search}%");
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
            $selectedUser = User::where('role', 'petugas')->find($request->user_id);
        }

        $files = $query->latest()->paginate(20)->withQueryString();

        return view('admin.files.index', compact('files', 'selectedUser'));
    }

    public function export(Request $request)
    {
        // Simple CSV export for files
        $fileName = 'export_files_' . date('Ymd_His') . '.csv';
        $files = File::with('user')->latest()->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ['ID', 'User', 'Original Name', 'Stored Name', 'Type', 'Status', 'Size (Bytes)', 'Upload Date'];

        $callback = function() use($files, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($files as $f) {
                $row['ID']  = $f->id;
                $row['User']    = $f->user->name ?? 'N/A';
                $row['Original Name']  = $f->original_name;
                $row['Stored Name']  = $f->stored_name;
                $row['Type']  = $f->type;
                $row['Status']  = $f->status;
                $row['Size']  = $f->size_bytes;
                $row['Upload Date']  = $f->created_at;

                fputcsv($file, array($row['ID'], $row['User'], $row['Original Name'], $row['Stored Name'], $row['Type'], $row['Status'], $row['Size'], $row['Upload Date']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
