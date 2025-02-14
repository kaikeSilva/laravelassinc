<!DOCTYPE html>
<html>
<head>
    <title>Import Colors</title>
    <style>
        .progress-container {
            width: 100%;
            max-width: 400px;
            margin: 20px 0;
            display: none;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            width: 0%;
            transition: width 0.5s ease-in-out;
        }
        .status-text {
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Import Colors from CSV</h1>

    @if($errors->any())
        <div>
            <ul>
                @foreach($errors->all() as $error)
                    <li style="color:red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="importForm" action="{{ route('colors.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="csv_file">CSV File:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv">
        </div>
        <button type="submit">Import</button>
    </form>

    <div class="progress-container" id="progressContainer">
        <div class="progress-bar">
            <div class="progress-fill" id="progressBar"></div>
        </div>
        <div class="status-text" id="statusText">0%</</div>
    </div>

    <a href="{{ route('colors.index') }}">Back to Colors List</a>

    <script>
        const form = document.getElementById('importForm');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const statusText = document.getElementById('statusText');
        let pollInterval;

        form.onsubmit = async (e) => {
            e.preventDefault();
            progressContainer.style.display = 'block';
            
            try {
                const formData = new FormData(form);
                const response = await fetch('{{ route('colors.import') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Import failed');
                }

                // Start polling for progress
                startProgressPolling(data.cacheKey);

            } catch (error) {
                console.error('Error:', error);
                alert('Import failed: ' + error.message);
                progressContainer.style.display = 'none';
            }
        };

        function startProgressPolling(cacheKey) {
            pollInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/import-progress/${cacheKey}`);
                    const data = await response.json();
                    
                    updateProgress(data.progress);
                    
                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(pollInterval);
                        if (data.status === 'completed') {
                            window.location.href = '{{ route('colors.index') }}';
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 2000);
        }

        function updateProgress(progress) {
            progressBar.style.width = `${progress}%`;
            statusText.textContent = `${progress}%`;
        }
    </script>
</body>
</html>