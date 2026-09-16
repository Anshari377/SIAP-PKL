<?php

use App\Http\Controllers\Admin\AdminApplicationController;
use App\Models\Agency;
use App\Models\Application;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::beginTransaction();

    $agency = null;

    $division = Division::create([
        'agency_id' => null,
        'slug' => 'repro-division-'.uniqid(),
        'nama' => 'Repro Division',
        'kategori' => 'teknis',
        'instansi' => 'Diskominfo Kaltim',
        'deskripsi' => 'repro',
        'quota' => 5,
    ]);

    $student = User::create([
        'name' => 'Repro Student',
        'email' => 'repro-student-'.uniqid().'@pkl.test',
        'password' => 'secret123',
        'agency_id' => null,
    ]);

    $appModel = Application::create([
        'user_id' => $student->id,
        'division_id' => $division->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'status' => 'pending',
        'consent_pdp' => true,
    ]);

    $admin = User::create([
        'name' => 'Repro Admin',
        'email' => 'repro-admin-'.uniqid().'@pkl.test',
        'password' => 'secret123',
        'agency_id' => null,
    ]);

    $pdfPath = sys_get_temp_dir().'/repro-surat-'.uniqid().'.pdf';
    file_put_contents($pdfPath, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");

    $request = Illuminate\Http\Request::create(
        "/admin/pengajuan/{$appModel->id}/status",
        'PATCH',
        ['status' => 'accepted'],
        [],
        ['surat_balasan' => new UploadedFile($pdfPath, 'surat.pdf', 'application/pdf', null, true)]
    );
    $request->setUserResolver(fn () => $admin);

    echo "Calling controller updateStatus...\n";
    $controller = $app->make(AdminApplicationController::class);
    $response = $controller->updateStatus($request, $appModel);
    echo 'HTTP status: '.$response->getStatusCode()."\n";

    $appModel->refresh();
    echo 'DB status after: '.$appModel->status."\n";
    echo 'surat_balasan_path: '.$appModel->surat_balasan_path."\n";

    if ($appModel->surat_balasan_path) {
        $exists = Storage::disk('public')->exists($appModel->surat_balasan_path);
        echo 'file exists on public disk: '.($exists ? 'YES' : 'NO')."\n";
        Storage::disk('public')->delete($appModel->surat_balasan_path);
    }

    DB::rollBack();

    echo "REPRO-END\n";
} catch (Throwable $e) {
    echo 'EXCEPTION: '.get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    exit(1);
}