(function () {
  const form = document.querySelector<HTMLFormElement>('form[name="form_contact"]');
  const successEl = document.getElementById('contact-success');
  const errorEl = document.getElementById('contact-error');
  const submitBtn = form?.querySelector<HTMLButtonElement>('button[type="submit"]');

  if (!form || !successEl || !errorEl) {
    return;
  }

  function showAlert(el: HTMLElement, message: string, cls: string): void {
    if (!el) return;

    el.classList.remove('visually-hidden', 'd-none');
    el.setAttribute('aria-hidden', 'false');
    el.classList.add('alert', cls, 'mb-3');
    el.innerHTML = message;

    // Hide the other alert if visible
    const otherEl = el === successEl ? errorEl : successEl;

    if (otherEl) {
      otherEl.classList.add('visually-hidden', 'd-none');
      otherEl.classList.remove('alert', 'alert-success', 'alert-danger');
      otherEl.innerHTML = '';
    }

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function clearAlerts(): void {
    successEl?.classList.add('visually-hidden', 'd-none');
    errorEl?.classList.add('visually-hidden', 'd-none');
    document.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach((el) => el.remove());
  }

  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    ev.stopPropagation();

    // Reset previous state
    clearAlerts();

    if (submitBtn) {
      submitBtn.disabled = true;
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Wird gesendet...';

      // Restore button after delay or finish
      const restoreBtn = () => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      };

      const formData = new FormData(form);

      try {
        const response = await fetch('/api/contact', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
          showAlert(successEl!, data.message, 'alert-success');
          form.reset();
          form.classList.remove('was-validated');
        } else {
          // Error handling
          let msg = data.message || 'Ein unbekannter Fehler ist aufgetreten.';

          // Prevent duplicate error output
          if (!errorEl!.innerHTML.includes(msg)) {
            showAlert(errorEl!, msg, 'alert-danger');
          }

          // Handle field errors
          if (data.errors) {
            Object.entries(data.errors).forEach(([field, messages]) => {
              if (field === 'global') return;

              // Field name in form is form_contact[field]
              const input = form.querySelector(`[name="form_contact[${field}]"]`);

              if (input) {
                input.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.innerText = (messages as string[]).join(' ');
                input.parentElement?.appendChild(errorDiv);
              }
            });
          }
        }
      } catch (e) {
        showAlert(
          errorEl!,
          'Verbindungsfehler. Bitte versuchen Sie es später erneut.',
          'alert-danger'
        );
      } finally {
        restoreBtn();
      }
    }
  });
})();
