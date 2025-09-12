const Tasks = (() => {
    const token = localStorage.getItem('access_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    let tasksTable, metadataEditor, typingTimer;
    const doneTypingInterval = 500;

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    const init = () => {
        if (!token || !user?.id) window.location.href = '/login';
        if (user?.role !== 'admin') document.querySelector('#createTaskBtn').style.display = 'none';

        metadataEditor = CodeMirror.fromTextArea(document.getElementById('metadata'), {
            mode: {name: "javascript", json: true},
            lineNumbers: true,
            theme: "idea",
            tabSize: 2,
            indentWithTabs: false,
            autoCloseBrackets: true
        });
        metadataEditor.setSize("100%", "150px");

        $('#filterKeyword').on('input', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(loadTasks, doneTypingInterval);
        });

        loadTags();
        loadUsers();
        loadTasks();
    };

    const loadUsers = () => {
        axios.get('/api/users').then(res => {
            const assignedSelect = document.getElementById('assigned_to');
            assignedSelect.innerHTML = '<option value="">Select User</option>';
            res.data.data.forEach(user => {
                assignedSelect.innerHTML += `<option value="${user.id}">${user.name}</option>`;
            });
        });
    };

    const loadTags = () => {
        axios.get('/api/tags').then(res => {
            const tagsSelect = document.getElementById('tags');
            const filterTag = document.getElementById('filterTag');
            tagsSelect.innerHTML = '';
            filterTag.innerHTML = '<option value="">All Tags</option>';
            res.data.data.forEach(tag => {
                tagsSelect.innerHTML += `<option value="${tag.id}">${tag.name}</option>`;
                filterTag.innerHTML += `<option value="${tag.id}">${tag.name}</option>`;
            });
        });
    };

    const loadTasks = () => {
        if(tasksTable) tasksTable.destroy();

        tasksTable = $('#tasksTable').DataTable({
            serverSide: true,
            processing: true,
            searching: false,
            ajax: {
                url: '/api/tasks',
                data: function(d) {
                    d.search = $('#filterKeyword').val();
                    d.status = $('#filterStatus').val();
                    d.tags = $('#filterTag').val();

                    if(d.order && d.order.length){
                        const colIndex = d.order[0].column;
                        const dir = d.order[0].dir;
                        d.order_by = d.columns[colIndex].data;
                        d.order_dir = dir;
                    }
                },
                beforeSend: xhr => xhr.setRequestHeader('Authorization', `Bearer ${token}`),
                dataSrc: 'data'
            },
            columns: [
                { data: 'title', orderable: true },
                { data: 'status', orderable: false },
                { data: 'priority', orderable: true },
                {
                    data: 'tags',
                    render: tags => tags?.map(t => `<span class="badge me-1" style="background-color:${t.color??'black'};color:white;">${t.name}</span>`).join(''),
                    orderable: false
                },
                {
                    data: 'due_date',
                    render: d => d ? new Date(d).toLocaleDateString() : '',
                    orderable: true
                },
                { data: 'assigned_user.name', defaultContent: '', orderable: false },
                {
                    data: 'created_at',
                    render: d => d ? new Date(d).toLocaleDateString() : '',
                    orderable: false
                },
                {
                    data: 'id',
                    render: (id,row) => `
                        <button class="btn btn-sm btn-primary" onclick="Tasks.editTask(${id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="Tasks.deleteTask(${id})">Delete</button>
                        <button class="btn btn-sm btn-warning" onclick="Tasks.toggleStatus(${id})">Toggle Status</button>
                        ${row.deleted_at ? `<button class="btn btn-sm btn-success" onclick="Tasks.restoreTask(${id})">Restore</button>` : ''}
                    `,
                    orderable: false
                }
            ],
            pageLength: 10
        });
    };

    const openCreateModal = () => {
        $('#taskId, #taskVersion, #title, #description, #due_date').val('');
        $('#status').val('pending');
        $('#priority').val('medium');
        $('#assigned_to').val('');
        $('#tags').val([]);
        metadataEditor.setValue('{}');
        $('#modalTitle').text('Create Task');
    };

    const saveTask = () => {
        let taskId = $('#taskId').val();
        let payload = {
            title: $('#title').val(),
            description: $('#description').val(),
            status: $('#status').val(),
            priority: $('#priority').val(),
            due_date: $('#due_date').val(),
            assigned_to: $('#assigned_to').val(),
            tags: Array.from($('#tags').val() || []),
            version: $('#taskVersion').val()
        };

        try {
            metadataJson = JSON.parse(metadataEditor.getValue());
            payload.metadata = metadataEditor.getValue();
            $('#metadataError').addClass('d-none');
        } catch {
            $('#metadataError').removeClass('d-none');
            return;
        }

        const request = taskId
            ? axios.put(`/api/tasks/${taskId}`, payload)
            : axios.post(`/api/tasks`, payload);

        request.then(() => { $('#taskModal').modal('hide'); loadTasks(); })
            .catch(err => alert(err.response?.data?.message || 'Error'));
    };

    const editTask = id => {
        axios.get(`/api/tasks/${id}`).then(res => {
            const task = res.data.data;
            $('#taskId').val(task.id);
            $('#taskVersion').val(task.version);
            $('#title').val(task.title);
            $('#description').val(task.description || '');
            $('#status').val(task.status);
            $('#priority').val(task.priority);
            $('#due_date').val(task.due_date ?? '');
            $('#assigned_to').val(task.assigned_to || '');
            $('#tags').val(task.tags.map(t => t.id));
            metadataEditor.setValue(JSON.stringify(task.metadata || {}, null, 2));
            $('#modalTitle').text('Edit Task');
            $('#taskModal').modal('show');
        });
    };

    const deleteTask = id => { if(confirm('Delete task?')) axios.delete(`/api/tasks/${id}`).then(loadTasks); };
    const toggleStatus = id => {
        const row = tasksTable.row((idx,data) => data.id===id).data();
        if(!row) return alert('Task data not found');
        axios.patch(`/api/tasks/${id}/toggle-status`, { version: row.version })
            .then(loadTasks)
            .catch(err => alert(err.response?.status===409 ? 'Optimistic lock conflict' : 'Error toggling status.'));
    };
    const restoreTask = id => axios.patch(`/api/tasks/${id}/restore`).then(loadTasks);

    const logout = () => {
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        if(tasksTable) tasksTable.clear().destroy();
        $('#taskModal').modal('hide');
        window.location.href = '/login';
    };

    const formatJSON = () => {
        try {
            const parsed = JSON.parse(metadataEditor.getValue());
            metadataEditor.setValue(JSON.stringify(parsed, null, 2));
            $('#metadataError').addClass('d-none');
        } catch { $('#metadataError').removeClass('d-none'); }
    };

    return {
        init, loadTasks, openCreateModal, saveTask, editTask, deleteTask,
        toggleStatus, restoreTask, logout, formatJSON
    };
})();

document.addEventListener('DOMContentLoaded', Tasks.init);
