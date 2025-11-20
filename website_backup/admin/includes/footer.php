            </div>
            <!-- 페이지 콘텐츠 끝 -->
        </main>
    </div>
    
    <!-- 공통 JavaScript -->
    <script>
        // 알림 자동 숨김
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
        
        // 확인 대화상자
        function confirmDelete(message) {
            return confirm(message || '정말 삭제하시겠습니까?');
        }
        
        // 이미지 미리보기
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                    document.getElementById(previewId).classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>