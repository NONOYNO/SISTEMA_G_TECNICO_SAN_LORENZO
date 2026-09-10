<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\FileUploadService;

final class FileController extends Controller
{
    public function download(string $id): void
    {
        $service = new FileUploadService();
        $service->download((int) $id);
    }
}
