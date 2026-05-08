<?php
namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Group('Задачи')]
class TaskController extends Controller
{
    /**
     * Просмотр списка задач
     */
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->get();

        return $this->success(TaskResource::collection($tasks));
    }

    /**
     * Создание задачи
     */
    public function store(TaskStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $task = $request->user()->tasks()->create($request->validated());

            DB::commit();

            return $this->success(new TaskResource($task));
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }

    /**
     * Просмотр одной задачи
     */
    public function show(Request $request, $task)
    {
        $task = $request->user()->tasks()->find($task);

        if(!$task){
            return $this->error("Задач с этим идентификатором не найдено.", Response::HTTP_NOT_FOUND);
        }

        return $this->success(new TaskResource($task));
    }

    /**
     * Обновление задачи
     */
    public function update(TaskUpdateRequest $request, $task)
    {
        $task = $request->user()->tasks()->find($task);

        if(!$task){
            return $this->error("Задач с этим идентификатором не найдено.", Response::HTTP_NOT_FOUND);
        }

        $data = array_filter($request->validated(), fn($value) => ! empty($value));

        if ($task->status == TaskStatus::Pending and $data['status'] != 'processing') {
            return $this->error('Невозможно перейти непосредственно к статусам "завершено"/"отменено"/"ошибка".');
        }

        if (in_array($task->status, [TaskStatus::Completed, TaskStatus::Cancelled, TaskStatus::Failed])) {
            return $this->error('Изменить этот тип статуса на другие невозможно.');
        }

        if (isset($data['status'])) {
            $data['status'] = TaskStatus::from($data['status']);
        }

        DB::beginTransaction();

        try {
            $task->update($data);

            DB::commit();

            return $this->success(new TaskResource($task));
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }

    /**
     * Удаление задачи
     */
    public function destroy(Request $request, $task)
    {
        $task = $request->user()->tasks()->find($task);

        if(!$task){
            return $this->error("Задач с этим идентификатором не найдено.", Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();

        try {
            $task->delete();

            DB::commit();

            return $this->success(message: 'Задача успешно удален');
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }
}
