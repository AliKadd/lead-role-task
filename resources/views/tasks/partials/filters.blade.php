<div class="row mb-3 g-2">
    <div class="col-md-3">
        <input type="text" id="filterKeyword" class="form-control" placeholder="Search keyword...">
    </div>
    <div class="col-md-2">
        <select id="filterStatus" class="form-select">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="filterTag" class="form-select"></select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary w-100" onclick="Tasks.loadTasks()">Filter</button>
    </div>
</div>
