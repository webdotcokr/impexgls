<footer class="footer-wrapper">
    <!-- 상단 링크 -->
    <div class="footer-top-links">
        <div class="container mx-auto px-4">
            <div class="flex justify-start gap-8">
                <a href="<?php echo BASE_URL; ?>/pages/policies/privacy-policy.php" class="footer-top-link">Policy</a>
                <!-- <a href="<?php echo BASE_URL; ?>/pages/support/request-quote.php" class="footer-top-link">Contact us</a> -->
            </div>
        </div> 
    </div>
    
    <!-- 메인 푸터 콘텐츠 -->
    <div class="footer-main">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- 왼쪽: 회사 정보 -->
                <div class="footer-content text-left">
                    <h3 class="footer-office-title mb-6">
                        Corporate Headquarters <br/>2475 Touhy Avenue Suite 100<br/>
                        Elk Grove Village, IL 60007 USA
                    </h3>
                    
                    <div class="footer-contact-info space-y-2">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <span class="footer-label sm:w-20">Main</span>
                            <a href="tel:6302279300" class="footer-value">(630) 227-9300</a>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <span class="footer-label sm:w-20">Fax</span>
                            <span class="footer-value">(630) 227-9345</span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <span class="footer-label sm:w-20">E-mail</span>
                            <a href="mailto:hq@impexgls.com" class="footer-value">hq@impexgls.com</a>
                        </div>
                    </div>
                </div>
                
                <!-- 오른쪽: 로고 및 저작권 -->
                <div class="footer-logo-section flex flex-col items-start lg:items-end justify-end mt-8 lg:mt-0">
                    <div class="footer-logo mb-4">
                        <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="IMPEX GLS" height="50" class="footer-logo-img">
                    </div>
                    <p class="footer-copyright">
                        IMPEX GLS © 2025. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Quick Menu Include -->
<?php include __DIR__ . '/quick-menu.php'; ?>

<!-- 공통 JavaScript -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

<!-- 모바일 최적화 스크립트 -->
<script src="<?php echo BASE_URL; ?>/includes/mobile-scripts.js"></script>

<style>
/* 푸터 스타일 */
.footer-wrapper {
    background-color: #2B2E34;
}

.footer-main {
    padding: 90px 0;
}

.footer-top-links {
    padding-top: 40px;
}

/* 상단 링크 스타일 */
.footer-top-link {
    color: var(--cg500, #777986);
    font-family: Poppins;
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: normal;
    letter-spacing: -0.28px;
    transition: color 0.3s ease;
}

.footer-top-link:hover {
    color: #9ca3af;
}

/* 회사 정보 스타일 */
.footer-office-title {
    color: var(--cg200, #D2D4DA);
    font-family: Poppins;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
    letter-spacing: -0.32px;
}

/* Main, Fax, E-mail 라벨 스타일 */
.footer-label {
    color: var(--cg500, #777986);
    font-family: Poppins;
    font-size: 15px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
    letter-spacing: -0.45px;
}

/* 연락처 값 스타일 */
.footer-value {
    color: var(--cg200, #D2D4DA);
    font-family: Poppins;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
    letter-spacing: -0.32px;
    transition: color 0.3s ease;
}

.footer-value:hover {
    color: #e5e7eb;
}

/* 저작권 스타일 */
.footer-copyright {
    color: var(--cg500, #777986);
    font-family: Poppins;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
    letter-spacing: -0.28px;
}

/* 모바일에서 푸터 조정 */
@media (max-width: 768px) {
    .footer-main {
        padding: 50px 0;
    }
    
    .footer-office-title {
        font-size: 13px !important;
        font-style: normal !important;
        font-weight: 400 !important;
        line-height: normal !important;
        letter-spacing: -0.26px !important;
    }
    
    .footer-label {
        font-size: 13px;
    }
    
    .footer-value {
        font-size: 14px;
    }
    
    .footer-logo-img {
        width: 250px;
        height: auto;
    }
    
    .footer-copyright {
        font-size: 11px;
        font-style: normal;
        font-weight: 400;
        line-height: normal;
        letter-spacing: -0.33px;
    }
}

</style>


</body>
</html>