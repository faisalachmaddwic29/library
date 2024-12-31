<?php
// includes/admin_footer.php
?>
        </main>
    </div>

    <!-- Global Scripts -->
    <script>
        // Handle dropdowns
        function setupDropdown(buttonId, dropdownId) {
            const button = document.getElementById(buttonId);
            const dropdown = document.getElementById(dropdownId);
            
            if (button && dropdown) {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');
                });
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            const dropdowns = document.querySelectorAll('[id$="Dropdown"], [id$="dropdown"]');
            dropdowns.forEach(dropdown => dropdown.classList.add('hidden'));
        });

        // Setup all dropdowns
        setupDropdown('userMenuBtn', 'userDropdown');
        setupDropdown('notificationBtn', 'notificationDropdown');

        // Mobile menu handler
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('.sidebar');
        
        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Flash message auto-hide
        const flashMessage = document.getElementById('flashMessage');
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.transition = 'opacity 0.5s ease-in-out';
                flashMessage.style.opacity = '0';
                setTimeout(() => flashMessage.remove(), 500);
            }, 3000);
        }

        // Confirm delete actions
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!confirm(form.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });

        // Handle file inputs and show preview
        document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
            input.addEventListener('change', function() {
                const preview = document.getElementById(this.dataset.preview);
                if (preview && this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => preview.src = e.target.result;
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });

        // Active menu item highlight
        const currentPath = window.location.pathname;
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('bg-blue-700');
            }
        });

        // Form validation helper
        function validateForm(formId, rules = {}) {
            const form = document.getElementById(formId);
            if (!form) return true;

            let isValid = true;
            const errors = [];

            Object.entries(rules).forEach(([fieldName, rule]) => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (!field) return;

                const value = field.value.trim();
                
                if (rule.required && !value) {
                    errors.push(`${rule.label || fieldName} wajib diisi`);
                    isValid = false;
                }

                if (rule.minLength && value.length < rule.minLength) {
                    errors.push(`${rule.label || fieldName} minimal ${rule.minLength} karakter`);
                    isValid = false;
                }

                if (rule.pattern && !rule.pattern.test(value)) {
                    errors.push(`${rule.label || fieldName} tidak valid`);
                    isValid = false;
                }
            });

            if (!isValid) {
                alert(errors.join('\n'));
            }

            return isValid;
        }
    </script>

    <?php if (isset($page_scripts)): ?>
        <!-- Page Specific Scripts -->
        <?php echo $page_scripts; ?>
    <?php endif; ?>
</body>
</html>