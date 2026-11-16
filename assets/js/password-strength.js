/**
 * password-strength.js
 * ------------------------------------------------------------
 * Reusable password strength meter. Call attachPasswordStrength()
 * with a password input's id and it will inject a bar + label
 * right after that input, updating live on every keystroke.
 *
 * The rules here MIRROR includes/security.php's password_policy_errors()
 * — at least 8 characters, a letter, a number, a special character —
 * but this is feedback only. The server-side check is authoritative;
 * this never replaces it.
 */

function attachPasswordStrength(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const wrap = document.createElement('div');
    wrap.className = 'pw-strength-wrap';
    wrap.innerHTML = `
        <div class="pw-strength-bar"><div class="pw-strength-fill" id="${inputId}_fill"></div></div>
        <div class="pw-strength-label" id="${inputId}_label"></div>
        <ul class="pw-strength-checklist" id="${inputId}_checklist">
            <li data-rule="length">At least 8 characters</li>
            <li data-rule="letter">Contains a letter</li>
            <li data-rule="number">Contains a number</li>
            <li data-rule="symbol">Contains a special character</li>
        </ul>
    `;
    input.insertAdjacentElement('afterend', wrap);

    const fill  = document.getElementById(inputId + '_fill');
    const label = document.getElementById(inputId + '_label');
    const checklist = document.getElementById(inputId + '_checklist');

    function evaluate(password) {
        const rules = {
            length: password.length >= 8,
            letter: /[A-Za-z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[^A-Za-z0-9]/.test(password),
        };

        let score = Object.values(rules).filter(Boolean).length;
        if (password.length >= 12 && score === 4) score = 5; // bonus tier for long+complete

        checklist.querySelectorAll('li').forEach(li => {
            const rule = li.dataset.rule;
            li.classList.toggle('met', rules[rule]);
        });

        if (password.length === 0) {
            fill.style.width = '0%';
            fill.className = 'pw-strength-fill';
            label.textContent = '';
            return;
        }

        const levels = [
            { max: 1, width: '20%',  className: 'pw-weak',   text: 'Very weak' },
            { max: 2, width: '40%',  className: 'pw-weak',   text: 'Weak' },
            { max: 3, width: '60%',  className: 'pw-fair',   text: 'Fair' },
            { max: 4, width: '80%',  className: 'pw-good',   text: 'Good' },
            { max: 5, width: '100%', className: 'pw-strong', text: 'Strong' },
        ];
        const level = levels.find(l => score <= l.max) || levels[levels.length - 1];

        fill.style.width = level.width;
        fill.className = 'pw-strength-fill ' + level.className;
        label.textContent = level.text;
        label.className = 'pw-strength-label ' + level.className;
    }

    input.addEventListener('input', () => evaluate(input.value));
    evaluate(input.value); // in case of pre-filled value
}