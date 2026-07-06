<?php

namespace App\Presentation\Controllers;

use Container;
use App\Application\UseCases\Counter\GetCounters;
use App\Application\UseCases\Counter\CreateCounter;
use App\Application\UseCases\Counter\UpdateCounter;
use App\Application\UseCases\Counter\DeleteCounter;
use App\Presentation\Middleware\AuthMiddleware;
use Exception;

class CounterController extends Controller
{
    /**
     * Tampilan daftar loket (Admin Only)
     */
    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        /** @var GetCounters $getCountersUseCase */
        $getCountersUseCase = Container::get(GetCounters::class);
        $counters = $getCountersUseCase->execute();

        $success = $_SESSION['counter_success'] ?? null;
        $error = $_SESSION['counter_error'] ?? null;
        unset($_SESSION['counter_success'], $_SESSION['counter_error']);

        $this->render('counter/index', [
            'title' => 'Manajemen Loket - SiLLA',
            'counters' => $counters,
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * Buat loket baru
     */
    public function create(): void
    {
        AuthMiddleware::requireAdmin();

        $name = $_POST['name'] ?? '';
        $isActive = isset($_POST['is_active']);

        try {
            /** @var CreateCounter $createUseCase */
            $createUseCase = Container::get(CreateCounter::class);
            $createUseCase->execute($name, $isActive);

            $_SESSION['counter_success'] = "Loket '{$name}' berhasil dibuat.";
        } catch (Exception $e) {
            $_SESSION['counter_error'] = $e->getMessage();
        }

        $this->redirect('/counters');
    }

    /**
     * Update data loket
     */
    public function update(): void
    {
        AuthMiddleware::requireAdmin();

        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $isActive = isset($_POST['is_active']);
        $officerUid = $_POST['officer_uid'] ?? null; // Opsional

        try {
            /** @var UpdateCounter $updateUseCase */
            $updateUseCase = Container::get(UpdateCounter::class);
            $updateUseCase->execute($id, $name, $isActive, $officerUid);

            $_SESSION['counter_success'] = "Loket '{$name}' berhasil diperbarui.";
        } catch (Exception $e) {
            $_SESSION['counter_error'] = $e->getMessage();
        }

        $this->redirect('/counters');
    }

    /**
     * Hapus loket
     */
    public function delete(): void
    {
        AuthMiddleware::requireAdmin();

        $id = $_POST['id'] ?? '';

        try {
            /** @var DeleteCounter $deleteUseCase */
            $deleteUseCase = Container::get(DeleteCounter::class);
            $deleteUseCase->execute($id);

            $_SESSION['counter_success'] = "Loket berhasil dihapus.";
        } catch (Exception $e) {
            $_SESSION['counter_error'] = $e->getMessage();
        }

        $this->redirect('/counters');
    }
}
