<!DOCTYPE html>
<html>
<head>
    <title>Register - Photobooth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-5">
                    <h2 class="text-center mb-4">📸 Register Photobooth</h2>
                    <form id="regForm">
                        <div class="mb-3">
                            <label class="form-label">Name (Create Folder Name)</label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact / ID</label>
                            <input type="text" name="contact" class="form-control" placeholder="Phone or Email">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill" id="btnSubmit">Register Queue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('regForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerText = 'Registering...';

            const formData = new FormData(this);

            fetch('api.php?action=register', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Success!', 'You are in queue! ID: ' + data.id, 'success');
                    this.reset();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
                btn.disabled = false;
                btn.innerText = 'Register Queue';
            })
            .catch(err => {
                Swal.fire('Error', 'Connection Failed', 'error');
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
