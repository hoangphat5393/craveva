<?php

namespace Modules\CodeMap\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Modules\CodeMap\Entities\FileRecord;
use Modules\CodeMap\Entities\FileDependency;
use Modules\CodeMap\Services\FileScanner;

class CodeMapController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'CodeMap';
        $this->activeSettingMenu = 'company_settings';
    }

    public function index(Request $request)
    {
        try {
            $query = FileRecord::query();

            if ($request->filled('q')) {
                $q = $request->get('q');
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                      ->orWhere('path', 'like', "%$q%")
                      ->orWhere('role', 'like', "%$q%");
                });
            }

            if ($request->filled('language')) {
                $query->where('language', $request->get('language'));
            }

            if ($request->filled('module')) {
                $query->where('module', $request->get('module'));
            }

            $this->records = $query->orderBy('path')->paginate(30);
        } catch (\Throwable $e) {
            $this->records = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30);
            session()->flash('error', 'Bảng dữ liệu CodeMap chưa migrate. Vui lòng chạy php artisan migrate.');
        }

        return view('codemap::index', $this->data);
    }

    public function scan(Request $request)
    {
        (new FileScanner())->scanAndStore();
        return back()->with('success', 'Đã quét và lưu thông tin file thành công.');
    }

    public function export()
    {
        $files = FileRecord::with('dependencies')->get();
        $payload = [
            'files' => $files->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        return Response::make(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="code_map_export.json"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt'
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($json, true);

        if (!is_array($data) || !isset($data['files'])) {
            return back()->with('error', 'Dữ liệu JSON không hợp lệ.');
        }

        foreach ($data['files'] as $item) {
            $record = FileRecord::updateOrCreate(
                ['path' => $item['path']],
                [
                    'name' => $item['name'] ?? basename($item['path']),
                    'language' => $item['language'] ?? null,
                    'framework' => $item['framework'] ?? null,
                    'role' => $item['role'] ?? null,
                    'module' => $item['module'] ?? null,
                    'version' => $item['version'] ?? null,
                    'last_modified_at' => $item['last_modified_at'] ?? null,
                    'hash' => $item['hash'] ?? null,
                    'extra' => $item['extra'] ?? null,
                ]
            );

            // Import dependencies logic can be added here if needed
        }

        return back()->with('success', 'Import thành công.');
    }
}
