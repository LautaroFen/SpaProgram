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

  // Strict numeric inputs: prevent scientific notation (e/E) which HTML number inputs allow.
  // This avoids users typing values like "1e6".
  document.querySelectorAll('input[type="number"]').forEach((inputEl) => {
    if (inputEl.dataset.strictNumberWired === '1') return;
    inputEl.dataset.strictNumberWired = '1';

    const sanitize = () => {
      const value = String(inputEl.value ?? '');
      if (!/[eE]/.test(value)) return;
      inputEl.value = value.replace(/[eE]/g, '');
    };

    inputEl.addEventListener('keydown', (event) => {
      if (event.key === 'e' || event.key === 'E') {
        event.preventDefault();
      }
    });

    // Covers IME and some mobile keyboards
    inputEl.addEventListener('beforeinput', (event) => {
      const data = event.data;
      if (data === 'e' || data === 'E') {
        event.preventDefault();
      }
    });

    // Covers paste and other programmatic insertions
    inputEl.addEventListener('input', sanitize);
    inputEl.addEventListener('paste', () => setTimeout(sanitize, 0));
  });
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/service-worker.js').catch(() => {
        // ignore
      });
    });
  }

  const themeToggles = document.querySelectorAll('[data-theme-toggle]');
  if (themeToggles.length) {
    const syncToggles = () => {
      const isDark = document.documentElement.classList.contains('dark');
      const nextLabel = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';

      themeToggles.forEach((toggle) => {
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        if (!toggle.hasAttribute('aria-label')) toggle.setAttribute('aria-label', nextLabel);
        toggle.setAttribute('title', nextLabel);
      });
    };

    const onToggle = (event) => {
      event.preventDefault();
      const isDark = document.documentElement.classList.contains('dark');
      const next = isDark ? 'light' : 'dark';
      setStoredTheme(next);

      // Reload so all components re-render with the new theme.
      window.location.reload();
    };

    themeToggles.forEach((toggle) => {
      toggle.addEventListener('click', onToggle);
    });

    syncToggles();
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

  document.querySelectorAll('form[data-export-reload]').forEach((form) => {
    form.addEventListener('submit', () => {
      // Give the download request time to start, then reload
      // so the UI reflects any purge action.
      window.setTimeout(() => {
        window.location.reload();
      }, 2500);
    });
  });

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

  const wireExpenseEditModal = () => {
    const api = wireModal({ modalId: 'expenseEditModal' });
    if (!api) return;

    const form = document.getElementById('expenseEditForm');
    const subtitle = document.getElementById('expenseEditSubtitle');
    const amountDue = document.getElementById('expenseEditAmountDue');
    const amountPaid = document.getElementById('expenseEditAmountPaid');
    const expenseIdInput = document.getElementById('expenseEditId');
    const monthInput = document.getElementById('expenseEditMonth');
    const qInput = document.getElementById('expenseEditQ');

    if (!form || !amountDue || !amountPaid) return;

    const actionTemplate = form.dataset.actionTemplate;

    // If the modal is opened due to validation errors, ensure the action matches the expense id.
    if (expenseIdInput && expenseIdInput.value && actionTemplate) {
      form.setAttribute('action', actionTemplate.replace('__ID__', String(expenseIdInput.value)));
      if (subtitle && !subtitle.textContent.trim()) {
        subtitle.textContent = `Editando expensa #${expenseIdInput.value}`;
      }
    }

    document.querySelectorAll('[data-expense-edit]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.expenseId;
        if (!id || !actionTemplate) return;

        form.setAttribute('action', actionTemplate.replace('__ID__', String(id)));
        if (expenseIdInput) expenseIdInput.value = String(id);

        const category = btn.dataset.expenseCategory || '';
        const payee = btn.dataset.expensePayee || '';
        const due = btn.dataset.expenseAmountDue || '';
        const paid = btn.dataset.expenseAmountPaid || '';

        if (subtitle) {
          const label = [category, payee].filter(Boolean).join(' · ');
          subtitle.textContent = label || '—';
        }

        amountDue.value = due;
        amountPaid.value = paid;

        // Keep current filters when returning to index.
        if (monthInput) monthInput.value = monthInput.value || '';
        if (qInput) qInput.value = qInput.value || '';

        api.open();
      });
    });
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

  wireExpenseEditModal();

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

  const wireServiceEditModal = () => {
    const api = wireModal({ openButtonId: 'openServiceModal', modalId: 'serviceModal' });
    if (!api) return;

    const form = document.getElementById('serviceForm');
    const titleEl = document.getElementById('serviceModalTitle');
    const serviceIdInput = document.getElementById('serviceEditId');
    const nameInput = document.getElementById('name');
    const durationInput = document.getElementById('duration_minutes');
    const priceInput = document.getElementById('price');
    const isActiveCheckbox = form?.querySelector('input[type="checkbox"][name="is_active_new"]');
    const createUrl = form?.dataset?.createUrl || '';

    if (!form || !serviceIdInput || !nameInput || !durationInput || !priceInput) return;

    const setPatchMethod = (enabled) => {
      const existing = form.querySelector('input[name="_method"]');
      if (enabled) {
        if (!existing) {
          const methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'PATCH';
          form.appendChild(methodInput);
        } else {
          existing.value = 'PATCH';
        }
      } else if (existing) {
        existing.remove();
      }
    };

    const resetToCreate = () => {
      if (titleEl) titleEl.textContent = 'Nuevo servicio';
      form.setAttribute('action', createUrl || form.getAttribute('action') || '');
      setPatchMethod(false);
      serviceIdInput.value = '';
      nameInput.value = '';
      durationInput.value = '';
      priceInput.value = '';
      if (isActiveCheckbox) isActiveCheckbox.checked = true;
    };

    const openEdit = (btn) => {
      const id = btn.dataset.serviceId;
      const updateUrl = btn.dataset.serviceUpdateUrl;
      if (!id || !updateUrl) return;

      if (titleEl) titleEl.textContent = 'Editar servicio';
      form.setAttribute('action', updateUrl);
      setPatchMethod(true);

      serviceIdInput.value = String(id);
      nameInput.value = btn.dataset.serviceName || '';
      durationInput.value = btn.dataset.serviceDurationMinutes || '';

      const priceCents = parseInt(btn.dataset.servicePriceCents || '0', 10);
      priceInput.value = (priceCents / 100).toFixed(2);

      if (isActiveCheckbox) {
        isActiveCheckbox.checked = (btn.dataset.serviceIsActive || '0') === '1';
      }

      api.open();
    };

    const openButton = document.getElementById('openServiceModal');
    if (openButton) {
      openButton.addEventListener('click', () => {
        resetToCreate();
      });
    }

    // If modal opened due to validation errors while editing, keep the title coherent.
    if (api.modal?.dataset?.openOnLoad === '1' && serviceIdInput.value) {
      if (titleEl) titleEl.textContent = 'Editar servicio';
    }

    document.querySelectorAll('[data-service-edit]').forEach((btn) => {
      btn.addEventListener('click', () => openEdit(btn));
    });
  };

  wireServiceEditModal();

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

});