<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if (isLoggedIn()) {
    header('Location: /Dashboard/' . $_SESSION['role'] . '.php');
    exit;
}

$initialRole = ($_GET['role'] ?? '') === 'company' ? 'company' : 'student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - TalentProve</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
    /* Custom styling for select dropdown */
    select.form-input {
        cursor: pointer;
    }
    select.form-input option {
        padding: 10px;
    }
    #customIndustryWrapper {
        transition: all 0.3s ease;
    }
    #customIndustryWrapper.show {
        display: block !important;
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
</head>
<body class="auth-bg register-page min-h-screen">

<nav class="auth-nav">
    <a class="auth-back-btn" href="/"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <a href="/" class="auth-nav-logo">
        <img src="/assets/images/logo.jpeg" alt="TalentProve logo">
    </a>
    <a class="auth-nav-action" href="/auth/login.php">Login <i class="fa-solid fa-arrow-right"></i></a>
</nav>

<main class="register-shell fade-in">
    <section class="register-brand-panel">
        <a href="/" class="brand-mark">
            <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
        </a>
        <div>
            <p class="section-kicker">TalentProve workspace</p>
            <h1>Create your account</h1>
            <p>Students prove their skills with real submissions. Companies review practical work before interviews.</p>
        </div>
        <div class="register-proof-list">
            <div><i class="fa-solid fa-user-check"></i><span>Skill-based profiles</span></div>
            <div><i class="fa-solid fa-briefcase"></i><span>Company task posting</span></div>
            <div><i class="fa-solid fa-bell"></i><span>Status notifications</span></div>
        </div>
    </section>

    <section class="register-card">
    <div class="mb-7">
        <a href="/" class="brand-mark mb-5 md:hidden">
            <img src="/assets/images/logo.jpeg" alt="TalentProve logo" class="brand-logo">
        </a>
        <p class="text-sm font-black uppercase tracking-[.12em] text-emerald-700">Get started</p>
        <h2 class="mt-2 text-3xl font-black text-slate-950">Create your account</h2>
        <p class="mt-2 text-sm font-bold leading-6 text-slate-500">Choose your role and complete the details below.</p>
    </div>

    <!-- Role Selector -->
    <div class="role-selector register-role-selector mb-6">
        <button type="button" class="role-btn <?= $initialRole === 'student' ? 'active' : '' ?>" data-role="student" onclick="switchRole('student')">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Student</span>
        </button>
        <button type="button" class="role-btn <?= $initialRole === 'company' ? 'active' : '' ?>" data-role="company" onclick="switchRole('company')">
            <i class="fa-solid fa-building"></i>
            <span>Company</span>
        </button>
    </div>

    <div id="formMessage" class="hidden mb-4"></div>

    <form id="registerForm">
        <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($initialRole) ?>">

        <!-- Student Fields -->
        <div id="studentFields" class="<?= $initialRole === 'company' ? 'hidden' : '' ?>">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" name="name" class="form-input" placeholder="John Doe" pattern="[A-Za-z][A-Za-z0-9 ]*" title="Start with a letter. Use letters, numbers, and spaces only" oninput="this.value = this.value.replace(/[^A-Za-z0-9 ]/g, '').replace(/^[0-9 ]+/, '').replace(/\s{2,}/g, ' ')" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-input" placeholder="you@gmail.com" pattern="[a-z0-9._%+\-]+@gmail\.com" title="Use a valid @gmail.com address" oninput="this.value = this.value.toLowerCase()" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="regPassword" class="form-input" placeholder="Min 8 characters" required>
                        <button type="button" class="eye-toggle" onclick="togglePassword('regPassword', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Skills <span class="text-slate-500 text-xs">(comma separated)</span></label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-code input-icon"></i>
                        <input type="text" name="skills" class="form-input" placeholder="JavaScript, Python, UI Design...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Portfolio Link <span class="text-slate-500 text-xs">(optional)</span></label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-link input-icon"></i>
                        <input type="url" name="portfolio_link" class="form-input" placeholder="https://yourportfolio.com">
                    </div>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="form-label">Bio <span class="text-slate-500 text-xs">(optional)</span></label>
                    <textarea name="bio" class="form-textarea" placeholder="Tell companies about yourself..."></textarea>
                </div>
            </div>
        </div>

        <!-- Company Fields -->
        <div id="companyFields" class="<?= $initialRole === 'student' ? 'hidden' : '' ?>">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-building input-icon"></i>
                        <input type="text" name="company_name" class="form-input" placeholder="Enter company Name" pattern="[A-Za-z][A-Za-z0-9 ]*" title="Start with a letter. Use letters, numbers, and spaces only" oninput="this.value = this.value.replace(/[^A-Za-z0-9 ]/g, '').replace(/^[0-9 ]+/, '').replace(/\s{2,}/g, ' ')">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" name="company_email" class="form-input" placeholder="company@gmail.com" pattern="[a-z0-9._%+\-]+@gmail\.com" title="Use a valid @gmail.com address" oninput="this.value = this.value.toLowerCase()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="company_password" id="compPassword" class="form-input" placeholder="Min 8 characters">
                        <button type="button" class="eye-toggle" onclick="togglePassword('compPassword', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Industry</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-industry input-icon"></i>
                        <select name="industry_select" id="industrySelect" class="form-input" onchange="handleIndustryChange()" style="appearance: none; padding-right: 40px; background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27currentColor%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;">
                            <option value="">Select industry...</option>
                            <option value="Technology">Technology</option>
                            <option value="Finance">Finance</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Other">Other (Specify)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="customIndustryWrapper" style="display: none;">
                    <label class="form-label">Specify Industry</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-building-columns input-icon"></i>
                        <input type="text" name="industry_custom" id="industryCustom" class="form-input" placeholder="E.g., Education, Manufacturing, Retail...">
                    </div>
                </div>
                <input type="hidden" name="industry" id="industryFinal">
                <div class="form-group">
                    <label class="form-label">Website <span class="text-slate-500 text-xs">(optional)</span></label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-globe input-icon"></i>
                        <input type="url" name="website" class="form-input" placeholder="https://company.com">
                    </div>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="form-label">Company Description</label>
                    <textarea name="description" class="form-textarea" placeholder="What does your company do?"></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full mt-6" id="submitBtn">
            <span>Create Account</span>
            <i class="fa-solid fa-arrow-right ml-2"></i>
        </button>
    </form>

    <p class="text-center text-slate-500 text-sm font-bold mt-6">
        Already have an account?
        <a href="/auth/login.php" class="text-emerald-700 hover:text-emerald-800 font-black transition-colors">Sign in</a>
    </p>
    </section>
</main>

<script src="/assets/js/main.js"></script>
<script>
let currentRole = '<?= $initialRole ?>';

function handleIndustryChange() {
    const select = document.getElementById('industrySelect');
    const customWrapper = document.getElementById('customIndustryWrapper');
    const customInput = document.getElementById('industryCustom');
    const finalInput = document.getElementById('industryFinal');
    
    if (select.value === 'Other') {
        customWrapper.style.display = 'block';
        customInput.required = true;
        finalInput.value = ''; // Will be set on form submit
    } else {
        customWrapper.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
        finalInput.value = select.value;
    }
}

function setFieldGroupState(groupId, enabled) {
    document.querySelectorAll(`#${groupId} input, #${groupId} textarea, #${groupId} select`).forEach(field => {
        field.disabled = !enabled;
    });
}

function switchRole(role) {
    currentRole = role;
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`[data-role="${role}"]`).classList.add('active');
    document.getElementById('studentFields').classList.toggle('hidden', role !== 'student');
    document.getElementById('companyFields').classList.toggle('hidden', role !== 'company');
    setFieldGroupState('studentFields', role === 'student');
    setFieldGroupState('companyFields', role === 'company');
}

switchRole(currentRole);

document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Handle industry field for company
    if (currentRole === 'company') {
        const select = document.getElementById('industrySelect');
        const customInput = document.getElementById('industryCustom');
        const finalInput = document.getElementById('industryFinal');
        
        if (select.value === 'Other') {
            if (!customInput.value.trim()) {
                showToast('Please specify your industry', 'error');
                customInput.focus();
                return;
            }
            // Capitalize first letter of custom industry
            const custom = customInput.value.trim();
            finalInput.value = custom.charAt(0).toUpperCase() + custom.slice(1);
        } else {
            finalInput.value = select.value;
        }
        
        if (!finalInput.value) {
            showToast('Please select an industry', 'error');
            select.focus();
            return;
        }
    }
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating...';

    const formData = new FormData(this);
    const msgEl = document.getElementById('formMessage');

    try {
        const res = await fetch('/api/register.php', { method: 'POST', body: formData });
        const data = await res.json();
        msgEl.className = data.success ? 'alert-success mb-4' : 'alert-error mb-4';
        msgEl.innerHTML = (data.success ? '<i class="fa-solid fa-check-circle mr-2"></i>' : '<i class="fa-solid fa-circle-exclamation mr-2"></i>') + data.message;
        msgEl.classList.remove('hidden');
        if (data.success) {
            setTimeout(() => window.location.href = data.redirect, 1200);
        }
    } catch {
        msgEl.className = 'alert-error mb-4';
        msgEl.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-2"></i> Something went wrong.';
        msgEl.classList.remove('hidden');
    }

    btn.disabled = false;
    btn.innerHTML = '<span>Create Account</span><i class="fa-solid fa-arrow-right ml-2"></i>';
});
</script>
</body>
</html>
