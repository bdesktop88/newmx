<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk MX Verifier Tool</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        h1 {
            color: #e63946;
            margin-top: 40px;
        }
        form {
            margin-top: 40px;
            padding: 20px;
            background-color: #333;
            display: inline-block;
            border-radius: 8px;
        }
        input[type="file"], input[type="submit"] {
            margin: 10px;
            padding: 10px;
            border-radius: 5px;
            border: none;
        }
        input[type="submit"] {
            background-color: #e63946;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #d62828;
        }
    </style>
</head>
<body>
    <h1>Bulk MX Verifier Tool</h1>

    <form id="uploadForm" enctype="multipart/form-data">
        <label for="fileUpload">Upload Bulk Email File (CSV/TXT):</label><br>
        <input type="file" name="fileUpload" id="fileUpload" required><br>
        <input type="submit" value="Upload and Verify" id="submitBtn">
    </form>

    <div id="progressBox" style="margin-top: 30px; color: white;">
        <p id="progressText"></p>
        <div id="spinner" style="display:none;">🔄 Processing...</div>
        <a id="downloadLink" href="#" style="display:none; color:#4CAF50;" download>Download Results ZIP</a>
    </div>

    <script>
    const form = document.getElementById('uploadForm');
    const progressText = document.getElementById('progressText');
    const spinner = document.getElementById('spinner');
    const downloadLink = document.getElementById('downloadLink');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', e => {
        e.preventDefault();
        submitBtn.disabled = true;
        progressText.innerText = '';
        downloadLink.style.display = 'none';
        spinner.style.display = 'inline-block';

        const formData = new FormData(form);

        fetch('process_bulk_emails.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'started') {
                pollProgress();
            } else {
                spinner.style.display = 'none';
                progressText.innerText = 'Error: ' + (data.message || 'Upload failed');
                submitBtn.disabled = false;
            }
        })
        .catch(() => {
            spinner.style.display = 'none';
            progressText.innerText = 'Upload failed.';
            submitBtn.disabled = false;
        });
    });

    function pollProgress() {
        fetch('mx_results/<?php echo session_id(); ?>/progress.log?' + new Date().getTime())
        .then(res => res.text())
        .then(text => {
            const lastLine = text.trim().split('\n').pop();
            progressText.innerText = lastLine === 'done' ? '✅ Done!' : lastLine;

            if (lastLine === 'done') {
                spinner.style.display = 'none';
                submitBtn.disabled = false;
                downloadLink.href = 'mx_results/<?php echo session_id(); ?>/mx_results.zip';
                downloadLink.style.display = 'inline';
            } else {
                setTimeout(pollProgress, 1000);
            }
        })
        .catch(() => {
            progressText.innerText = 'Error checking progress.';
            spinner.style.display = 'none';
        });
    }
    </script>
</body>
</html>
