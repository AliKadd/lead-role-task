@extends('layouts.app', [
    'title' => 'Login',
    'bodyClass' => 'bg-light',
    'isCentered' => true
])

@section('content')
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <h3 class="mb-3 text-center">Login</h3>
        <div class="mb-3">
            <input type="email" id="email" class="form-control" placeholder="Email">
        </div>
        <div class="mb-3">
            <input type="password" id="password" class="form-control" placeholder="Password">
        </div>
        <button class="btn btn-primary w-100" onclick="login()">Login</button>
        <div class="mt-3 text-danger" id="errorMsg"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function login() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('errorMsg');
            errorMsg.innerText = '';

            axios.post('/api/login', { email, password })
                .then(res => {
                    console.log(res)
                    localStorage.setItem('access_token', res.data.data.access_token);
                    localStorage.setItem('user', JSON.stringify(res.data.data.user));
                    window.location.href = '/tasks';
                })
                .catch(err => {
                    console.error(err);
                    errorMsg.innerText = err.response?.data?.message || 'Login failed';
                });
        }
    </script>
@endpush
