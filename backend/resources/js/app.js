import './bootstrap';

const applyTheme = (theme) => {
  const root = document.documentElement;
  const wantsDark = theme === 'dark';
  root.classList.toggle('dark', wantsDark);
};

const getStoredTheme = () => {
  try {
    const v = localStorage.getItem('theme');
    return v === 'dark' || v === 'light' ? v : null;
  } catch {
    return null;
  }
};

const setStoredTheme = (theme) => {
  try {
    localStorage.setItem('theme', theme);
  } catch {
    // ignore
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const themeToggle = document.querySelector('[data-theme-toggle]');
  if (themeToggle) {
    const syncLabel = () => {
      const isDark = document.documentElement.classList.contains('dark');
      themeToggle.textContent = isDark ? 'Modo claro' : 'Modo oscuro';
      themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    };

    themeToggle.addEventListener('click', (event) => {
      event.preventDefault();
      const isDark = document.documentElement.classList.contains('dark');
      const next = isDark ? 'light' : 'dark';
      setStoredTheme(next);
      applyTheme(next);

      // Update any placeholder selects that rely on text color classes.
      document.querySelectorAll('select[data-placeholder-select]').forEach((select) => {
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });

      syncLabel();
    });

    syncLabel();
  }

  const button = document.getElementById('mobileMenuButton');
  const menu = document.getElementById('mobileMenu');

  if (button && menu) {
    const closeMenu = () => menu.classList.add('hidden');
    const toggleMenu = () => menu.classList.toggle('hidden');

    button.addEventListener('click', (event) => {
      event.preventDefault();
      toggleMenu();
    });

    document.addEventListener('click', (event) => {
      if (menu.classList.contains('hidden')) return;
      if (button.contains(event.target)) return;
      if (menu.contains(event.target)) return;
      closeMenu();
    });

    menu.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => closeMenu());
    });

    window.addEventListener('resize', () => {
      if (window.matchMedia('(min-width: 768px)').matches) {
        closeMenu();
      }
    });
  }

  fetch('/api/health')
    .then(r => r.json())
    .then(data => console.log('API health:', data))
    .catch(err => console.error('API error:', err));

  const wirePlaceholderSelects = () => {
    document.querySelectorAll('select[data-placeholder-select]').forEach((select) => {
      const update = () => {
        const isDark = document.documentElement.classList.contains('dark');
        const isEmpty = !select.value;
        select.classList.toggle('text-slate-400', isEmpty);

        select.classList.remove('text-slate-900', 'text-slate-100');
        if (!isEmpty) {
          select.classList.add(isDark ? 'text-slate-100' : 'text-slate-900');
        }
      };

      select.addEventListener('change', update);
      select.addEventListener('focus', () => {
        // Some browsers apply the <select> text color to the dropdown list.
        // While the placeholder is selected we still want the options list in black.
        select.classList.remove('text-slate-400');
        select.classList.remove('text-slate-100');
        select.classList.add('text-slate-900');
      });
      select.addEventListener('blur', update);
      update();
    });
  };

  wirePlaceholderSelects();

  const wireInputFilters = () => {
    const sanitizeDigits = (value) => String(value || '').replace(/\D+/g, '');

    // Letters with accents (unicode), spaces and common separators.
    // Keeps: letters, marks, spaces, hyphen, apostrophe.
    const sanitizeLetters = (value) => String(value || '').replace(/[^\p{L}\p{M}\s\-']/gu, '');

    document.querySelectorAll('input[data-only-digits]').forEach((input) => {
      input.addEventListener('input', () => {
        const next = sanitizeDigits(input.value);
        if (next !== input.value) input.value = next;
      });
    });

    document.querySelectorAll('input[data-only-letters]').forEach((input) => {
      input.addEventListener('input', () => {
        const next = sanitizeLetters(input.value);
        if (next !== input.value) input.value = next;
      });
    });
  };

  wireInputFilters();

  // Prevent row-level click handlers when interacting with inner controls
  document.querySelectorAll('[data-stop-row-click]').forEach((el) => {
    el.addEventListener('click', (event) => event.stopPropagation());
  });

  // Confirm before deleting appointments
  document.querySelectorAll('form[data-confirm-delete-appointment]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const clientName = form.dataset.clientName || '—';
      const ok = window.confirm(`¿Estas seguro de que quiere borrar el turno de ${clientName}?`);
      if (!ok) event.preventDefault();
    });
  });

  // Confirm before deleting payments
  document.querySelectorAll('form[data-confirm-delete-payment]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const clientName = form.dataset.clientName || '—';
      const ok = window.confirm(`¿Estas seguro de que quiere eliminar el pago de ${clientName}?`);
      if (!ok) event.preventDefault();
    });
  });

  const wireModal = ({ openButtonId, modalId }) => {
    const openButton = openButtonId ? document.getElementById(openButtonId) : null;
    const modal = document.getElementById(modalId);
    if (!modal) return null;

    const overlay = modal.querySelector('[data-modal-overlay]');
    const closeButtons = modal.querySelectorAll('[data-modal-close]');

    const open = () => {
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('overflow-hidden');
    };

    const close = () => {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('overflow-hidden');
    };

    if (openButton) openButton.addEventListener('click', () => open());
    if (overlay) overlay.addEventListener('click', () => close());
    closeButtons.forEach((btn) => btn.addEventListener('click', () => close()));

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

    if (modal.dataset.openOnLoad === '1') open();

    return { modal, open, close };
  };

  const wireUserRoleStatusModals = () => {
    const userStatusModalApi = wireModal({ modalId: 'userStatusModal' });
    const roleStatusModalApi = wireModal({ modalId: 'roleStatusModal' });

    const userForm = document.getElementById('userStatusForm');
    const userSubtitleEl = document.getElementById('userStatusSubtitle');
    const userIsActiveInput = document.getElementById('userStatusIsActive');
    const userFirstNameInput = document.getElementById('userEditFirstName');
    const userLastNameInput = document.getElementById('userEditLastName');
    const userEmailInput = document.getElementById('userEditEmail');
    const userRoleIdSelect = document.getElementById('userEditRoleId');
    const userJobTitleInput = document.getElementById('userEditJobTitle');
    const userEditIdInput = document.getElementById('userEditId');
    const userPasswordInput = document.getElementById('userEditPassword');
    const userPasswordConfirmationInput = document.getElementById('userEditPasswordConfirmation');

    if (
      userStatusModalApi
      && userForm
      && userSubtitleEl
      && userIsActiveInput
      && userFirstNameInput
      && userLastNameInput
      && userEmailInput
      && userRoleIdSelect
      && userJobTitleInput
      && userEditIdInput
    ) {
      if (userStatusModalApi.modal?.dataset?.openOnLoad === '1') {
        const id = userEditIdInput.value;
        if (id) {
          const template = userForm.dataset.actionTemplate || '';
          userForm.action = template.replace('__ID__', id);
          userSubtitleEl.textContent = `ID: ${id}`;
        }
      }

      document.querySelectorAll('[data-user-edit-button]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.userId;
          if (!id) return;

          const firstName = btn.dataset.userFirstName || '';
          const lastName = btn.dataset.userLastName || '';
          const email = btn.dataset.userEmail || '';
          const jobTitle = btn.dataset.userJobTitle || '';
          const roleId = btn.dataset.userRoleId || '';
          const isActive = (btn.dataset.userIsActive || '0') === '1';

          userSubtitleEl.textContent = `ID: ${id}`;
          userIsActiveInput.checked = isActive;

          userFirstNameInput.value = firstName;
          userLastNameInput.value = lastName;
          userEmailInput.value = email;
          userJobTitleInput.value = jobTitle;
          userRoleIdSelect.value = roleId;
          userEditIdInput.value = id;

          if (userPasswordInput) userPasswordInput.value = '';
          if (userPasswordConfirmationInput) userPasswordConfirmationInput.value = '';

          const template = userForm.dataset.actionTemplate || '';
          userForm.action = template.replace('__ID__', id);

          userStatusModalApi.open();
        });
      });
    }

    const roleForm = document.getElementById('roleStatusForm');
    const roleSubtitleEl = document.getElementById('roleStatusSubtitle');
    const roleIsActiveInput = document.getElementById('roleStatusIsActive');
    const roleNameInput = document.getElementById('roleEditName');
    const roleEditIdInput = document.getElementById('roleEditId');

    if (roleStatusModalApi && roleForm && roleSubtitleEl && roleIsActiveInput && roleNameInput && roleEditIdInput) {
      if (roleStatusModalApi.modal?.dataset?.openOnLoad === '1') {
        const id = roleEditIdInput.value;
        if (id) {
          const template = roleForm.dataset.actionTemplate || '';
          roleForm.action = template.replace('__ID__', id);
          roleSubtitleEl.textContent = `ID: ${id}`;
        }
      }

      document.querySelectorAll('[data-role-edit-button]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.roleId;
          if (!id) return;

          const name = btn.dataset.roleName || '—';
          const isActive = (btn.dataset.roleIsActive || '0') === '1';

          roleSubtitleEl.textContent = `ID: ${id}`;
          roleIsActiveInput.checked = isActive;
          roleNameInput.value = name;
          roleEditIdInput.value = id;

          const template = roleForm.dataset.actionTemplate || '';
          roleForm.action = template.replace('__ID__', id);

          roleStatusModalApi.open();
        });
      });
    }
  };

  wireUserRoleStatusModals();

  // Appointment modal
  const appointmentModalApi = wireModal({ openButtonId: 'openAppointmentModal', modalId: 'appointmentModal' });
  if (appointmentModalApi) {
    const serviceSelect = document.getElementById('service_id');
    const startDateInput = document.getElementById('start_date');
    const startTimeInput = document.getElementById('start_time');
    const serviceDurationEl = document.getElementById('appointmentServiceDuration');
    const servicePriceEl = document.getElementById('appointmentServicePrice');
    const endTimeEl = document.getElementById('appointmentEndTime');

    const clientSelect = document.getElementById('client_id');
    const newClientFields = document.getElementById('newClientFields');

    const formatMoneyARS = (cents) => {
      const value = (cents || 0) / 100;
      return value.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });
    };

    const computeEndTime = () => {
      if (!serviceSelect || !startDateInput || !startTimeInput || !endTimeEl) return;
      const option = serviceSelect.selectedOptions?.[0];
      const duration = option ? parseInt(option.dataset.durationMinutes || '0', 10) : 0;
      if (!duration || !startDateInput.value || !startTimeInput.value) {
        endTimeEl.textContent = '—';
        return;
      }

      const start = new Date(`${startDateInput.value}T${startTimeInput.value}`);
      if (Number.isNaN(start.getTime())) {
        endTimeEl.textContent = '—';
        return;
      }

      const end = new Date(start.getTime() + duration * 60000);
      const hh = String(end.getHours()).padStart(2, '0');
      const mm = String(end.getMinutes()).padStart(2, '0');
      endTimeEl.textContent = `${hh}:${mm}`;
    };

    const syncServiceInfo = () => {
      if (!serviceSelect) return;
      const option = serviceSelect.selectedOptions?.[0];
      const priceCents = option ? parseInt(option.dataset.priceCents || '0', 10) : 0;
      const duration = option ? parseInt(option.dataset.durationMinutes || '0', 10) : 0;

      if (serviceDurationEl) serviceDurationEl.textContent = duration ? `${duration} min` : '—';
      if (servicePriceEl) servicePriceEl.textContent = priceCents ? formatMoneyARS(priceCents) : '—';

      computeEndTime();
    };

    const syncClientMode = () => {
      if (!clientSelect || !newClientFields) return;
      const isNew = !clientSelect.value;
      newClientFields.classList.toggle('hidden', !isNew);
    };

    if (serviceSelect) {
      serviceSelect.addEventListener('change', syncServiceInfo);
      syncServiceInfo();
    }

    if (startDateInput) startDateInput.addEventListener('change', computeEndTime);
    if (startTimeInput) startTimeInput.addEventListener('change', computeEndTime);

    if (clientSelect) {
      clientSelect.addEventListener('change', syncClientMode);
      syncClientMode();
    }
  }

  // Appointment edit modal (click row)
  const wireAppointmentEditModal = () => {
    const modalApi = wireModal({ modalId: 'appointmentEditModal' });
    const form = document.getElementById('appointmentEditForm');
    const subtitleEl = document.getElementById('appointmentEditSubtitle');
    const editIdInput = document.getElementById('appointmentEditId');

    const serviceSelect = document.getElementById('edit_service_id');
    const startDateInput = document.getElementById('edit_start_date');
    const startTimeInput = document.getElementById('edit_start_time');
    const depositInput = document.getElementById('edit_deposit');
    const clientSelect = document.getElementById('edit_client_id');
    const userSelect = document.getElementById('edit_user_id');

    if (!modalApi || !form || !subtitleEl || !editIdInput || !serviceSelect || !startDateInput || !startTimeInput || !depositInput || !clientSelect || !userSelect) return;

    if (modalApi.modal?.dataset?.openOnLoad === '1') {
      const id = editIdInput.value;
      if (id) {
        const template = form.dataset.actionTemplate || '';
        form.action = template.replace('__ID__', id);
        subtitleEl.textContent = `ID: ${id}`;
      }
    }

    document.querySelectorAll('[data-appointment-edit-button]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.appointmentId;
        if (!id) return;

        serviceSelect.value = btn.dataset.appointmentServiceId || '';
        clientSelect.value = btn.dataset.appointmentClientId || '';
        userSelect.value = btn.dataset.appointmentUserId || '';

        startDateInput.value = btn.dataset.appointmentStartDate || '';
        startTimeInput.value = btn.dataset.appointmentStartTime || '';
        depositInput.value = btn.dataset.appointmentDeposit || '';

        editIdInput.value = id;
        const clientName = btn.dataset.clientName || '—';
        subtitleEl.textContent = `ID: ${id} — ${clientName}`;

        const template = form.dataset.actionTemplate || '';
        form.action = template.replace('__ID__', id);

        modalApi.open();
      });
    });
  };

  wireAppointmentEditModal();

  // Clients modal
  wireModal({ openButtonId: 'openClientModal', modalId: 'clientModal' });

  // Clients edit modal (click row)
  const wireClientEditModal = () => {
    const modalApi = wireModal({ modalId: 'clientEditModal' });
    const form = document.getElementById('clientEditForm');
    const subtitleEl = document.getElementById('clientEditSubtitle');
    const editIdInput = document.getElementById('clientEditId');

    const firstNameInput = document.getElementById('clientEditFirstName');
    const lastNameInput = document.getElementById('clientEditLastName');
    const phoneInput = document.getElementById('clientEditPhone');
    const emailInput = document.getElementById('clientEditEmail');
    const dniInput = document.getElementById('clientEditDni');

    if (!modalApi || !form || !subtitleEl || !editIdInput || !firstNameInput || !lastNameInput || !phoneInput || !emailInput || !dniInput) return;

    if (modalApi.modal?.dataset?.openOnLoad === '1') {
      const id = editIdInput.value;
      if (id) {
        const template = form.dataset.actionTemplate || '';
        form.action = template.replace('__ID__', id);
        subtitleEl.textContent = `ID: ${id}`;
      }
    }

    document.querySelectorAll('[data-client-edit-button]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.clientId;
        if (!id) return;

        firstNameInput.value = btn.dataset.clientFirstName || '';
        lastNameInput.value = btn.dataset.clientLastName || '';
        phoneInput.value = btn.dataset.clientPhone || '';
        emailInput.value = btn.dataset.clientEmail || '';
        dniInput.value = btn.dataset.clientDni || '';

        editIdInput.value = id;
        subtitleEl.textContent = `ID: ${id}`;

        const template = form.dataset.actionTemplate || '';
        form.action = template.replace('__ID__', id);

        modalApi.open();
      });
    });
  };

  wireClientEditModal();

  // Services modal
  wireModal({ openButtonId: 'openServiceModal', modalId: 'serviceModal' });

  // Users modal
  wireModal({ openButtonId: 'openUserModal', modalId: 'userModal' });

  // Roles modal
  wireModal({ openButtonId: 'openRoleModal', modalId: 'roleModal' });

  // Payments modal
  const paymentModalApi = wireModal({ openButtonId: 'openPaymentModal', modalId: 'paymentModal' });
  const paymentEditModalApi = wireModal({ modalId: 'paymentEditModal' });
  wireModal({ openButtonId: 'openExpenseModal', modalId: 'expenseModal' });
  if (paymentModalApi) {
    const modalEl = paymentModalApi.modal;
    const clientSelect = modalEl.querySelector('#client_id');
    const newClientFields = modalEl.querySelector('#newPaymentClientFields');
    const appointmentSelect = modalEl.querySelector('#appointment_id');
    const amountInput = modalEl.querySelector('#amount');
    const remainingHint = modalEl.querySelector('#appointmentRemainingHint');

    const formatMoneyARS = (cents) => {
      const value = (cents || 0) / 100;
      return value.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });
    };

    const syncClientMode = () => {
      if (!clientSelect || !newClientFields) return;
      const isNew = !clientSelect.value;
      newClientFields.classList.toggle('hidden', !isNew);
    };

    const syncFromAppointment = () => {
      if (!appointmentSelect) return;
      const option = appointmentSelect.selectedOptions?.[0];
      if (!option || !option.value) {
        if (remainingHint) remainingHint.textContent = 'Seleccioná un turno para cargar el saldo automáticamente.';
        return;
      }

      const clientId = option.dataset.clientId || '';
      const remainingCents = parseInt(option.dataset.remainingCents || '0', 10) || 0;

      if (clientSelect && clientId) {
        clientSelect.value = clientId;
        syncClientMode();
      }

      if (amountInput) {
        amountInput.value = (remainingCents / 100).toFixed(2);
      }

      if (remainingHint) {
        remainingHint.textContent = `Saldo del turno: ${formatMoneyARS(remainingCents)} (podés editar el monto)`;
      }
    };

    if (clientSelect) {
      clientSelect.addEventListener('change', syncClientMode);
      syncClientMode();
    }

    if (appointmentSelect) {
      appointmentSelect.addEventListener('change', syncFromAppointment);
      syncFromAppointment();
    }
  }

  // Payments edit modal
  if (paymentEditModalApi) {
    const modalEl = paymentEditModalApi.modal;
    const form = modalEl.querySelector('#paymentEditForm');
    const subtitleEl = modalEl.querySelector('#paymentEditSubtitle');
    const editIdInput = modalEl.querySelector('#paymentEditId');
    const amountInput = modalEl.querySelector('#paymentEditAmount');

    if (form && subtitleEl && editIdInput && amountInput) {
      if (paymentEditModalApi.modal?.dataset?.openOnLoad === '1') {
        const id = editIdInput.value;
        if (id) {
          const template = form.dataset.actionTemplate || '';
          form.action = template.replace('__ID__', id);
          subtitleEl.textContent = `ID: ${id}`;
        }
      }

      document.querySelectorAll('[data-payment-edit-button]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.paymentId;
          if (!id) return;

          const clientName = btn.dataset.clientName || '—';
          const amount = btn.dataset.paymentAmount || '';

          editIdInput.value = id;
          amountInput.value = amount;
          subtitleEl.textContent = `ID: ${id} — ${clientName}`;

          const template = form.dataset.actionTemplate || '';
          form.action = template.replace('__ID__', id);

          paymentEditModalApi.open();
        });
      });
    }
  }

});