<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-3">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="taskId">
                <input type="hidden" id="taskVersion">

                <div class="mb-2">
                    <input type="text" id="title" class="form-control" placeholder="Title">
                </div>
                <div class="mb-2">
                    <textarea id="description" class="form-control" placeholder="Description"></textarea>
                </div>
                <div class="mb-2">
                    <select id="status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="mb-2">
                    <select id="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="mb-2">
                    <input type="date" id="due_date" class="form-control">
                </div>
                <div class="mb-2">
                    <select id="assigned_to" class="form-select"></select>
                </div>
                <div class="mb-2">
                    <select id="tags" class="form-select" multiple></select>
                </div>
                <div class="mb-2">
                    <label for="metadata">Metadata (JSON)</label>
                    <textarea id="metadata" placeholder='{"key": "value"}'></textarea>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="Tasks.formatJSON()">Format JSON</button>
                    <small id="metadataError" class="text-danger d-none">Invalid JSON</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="Tasks.saveTask()">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
