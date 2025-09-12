@extends('layouts.app', ['title' => 'Tasks Dashboard'])

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tasks Management</h1>
        <div>
            <button id="createTaskBtn" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#taskModal" onclick="Tasks.openCreateModal()">Create Task</button>
            <button class="btn btn-outline-danger" onclick="Tasks.logout()">Logout</button>
        </div>
    </div>

    @include('tasks.partials.filters')

    @include('tasks.partials.table')

    @include('tasks.partials.modal')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/idea.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/javascript/javascript.min.js"></script>
    <script src="{{ asset('js/tasks.js') }}"></script>
@endpush
