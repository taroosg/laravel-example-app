<form id="imageUploadForm">
  <input type="file" name="image" required>
  <input type="submit" value="アップロード">
</form>
<div id="result"></div>

<script>
  $csrfToken = '{{ csrf_token() }}';
  document.getElementById('imageUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('/hakaru-ai/upload-image', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $csrfToken,
        },
        body: new FormData(e.target),
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('サーバーエラー');
        }
        return response.json();
      })
      .then(data => {
        console.log('APIからのデータ:', data);
        // 応答をDOMに表示
      })
      .catch(error => {
        console.error('エラー:', error);
        // エラーメッセージを表示
      });
  });
</script>