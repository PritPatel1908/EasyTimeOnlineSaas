function getValidationError(field) {
	if (!field.form || !field.name) {
		return null;
	}

	return Array.from(field.form.querySelectorAll('[data-validation-error-for]'))
		.find((error) => error.dataset.validationErrorFor === field.name);
}

function getFieldLabel(field) {
	const label = field.labels?.[0]?.textContent.replace(/\s+/g, ' ').trim();

	return label?.replace(/\s*\*\s*$/, '') || field.name
		.replace(/\[\]$/, '')
		.replace(/[_\[\]]+/g, ' ')
		.replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function showValidationToast(title, message) {
	if (typeof window.Swal?.fire !== 'function') {
		console.error('SweetAlert2 is unavailable; form validation feedback could not be displayed.');
		return;
	}

	window.Swal.fire({
		toast: true,
		position: 'top',
		icon: 'error',
		title,
		text: message,
		showConfirmButton: false,
		timer: 3000,
		timerProgressBar: true,
	});
}

function showValidationSummary(errors, title) {
	if (typeof window.Swal?.fire !== 'function') {
		console.error('SweetAlert2 is unavailable; form validation feedback could not be displayed.');
		return;
	}

	const content = document.createElement('div');
	content.className = 'validation-summary';

	const description = document.createElement('p');
	description.className = 'validation-summary-description';
	description.textContent = 'Please fix these items and try again.';
	content.append(description);

	const list = document.createElement('div');
	list.className = 'validation-summary-list';

	errors.forEach(({ field, label, message }) => {
		const item = document.createElement(field ? 'button' : 'div');
		item.className = 'validation-summary-item';

		if (field) {
			item.type = 'button';
			item.addEventListener('click', () => {
				window.Swal.close();
				window.setTimeout(() => {
					field.focus();
					field.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}, 150);
			});
		}

		const itemLabel = document.createElement('span');
		itemLabel.className = 'validation-summary-label';
		itemLabel.textContent = label;

		const itemMessage = document.createElement('span');
		itemMessage.className = 'validation-summary-message';
		itemMessage.textContent = message;

		item.append(itemLabel, itemMessage);
		list.append(item);
	});

	content.append(list);

	window.Swal.fire({
		title,
		html: content,
		icon: 'error',
		width: 560,
		showCloseButton: true,
		confirmButtonText: 'Got it',
		customClass: {
			popup: 'validation-summary-popup',
			htmlContainer: 'validation-summary-container',
		},
	});
}

function initializeFormValidation() {
	document.querySelectorAll('.alert.alert-danger').forEach((alert) => {
		if (/please correct the highlighted fields/i.test(alert.textContent)) {
			alert.remove();
		}
	});

	const serverErrors = Object.entries(window.__validationErrors || {}).flatMap(([name, messages]) => {
		const normalizedName = name.replace(/\[\d+\]/g, '').replace(/\[\]$/, '');
		const field = Array.from(document.forms).flatMap((form) => Array.from(form.elements))
			.find((element) => element.name?.replace(/\[\d+\]/g, '').replace(/\[\]$/, '') === normalizedName);
		const label = field ? getFieldLabel(field) : name.replace(/[_\[\]]+/g, ' ');

		return (Array.isArray(messages) ? messages : [messages]).map((message) => ({ field, label, message }));
	});

	if (serverErrors.length) {
		showValidationSummary(serverErrors, 'Please review the form');
	}

	document.querySelectorAll('form:not([data-skip-live-validation])').forEach((form) => {
		form.noValidate = true;
		const lastShownErrors = new WeakMap();

		const validateField = (field, notify = false) => {
			if (!field.willValidate) {
				return null;
			}

			if (field.checkValidity()) {
				field.classList.remove('is-invalid');
				field.removeAttribute('aria-invalid');
				lastShownErrors.delete(field);
				return null;
			}

			const message = field.validationMessage;
			field.classList.add('is-invalid');
			field.setAttribute('aria-invalid', 'true');

			if (notify && lastShownErrors.get(field) !== message) {
				showValidationToast(getFieldLabel(field), message);
				lastShownErrors.set(field, message);
			}

			return { field, label: getFieldLabel(field), message };
		};

		form.addEventListener('input', (event) => {
			if (event.target.matches('input, select, textarea')) {
				validateField(event.target, true);
			}
		});

		form.addEventListener('change', (event) => {
			if (event.target.matches('input, select, textarea')) {
				validateField(event.target, true);
			}
		});

		form.addEventListener('focusout', (event) => {
			if (event.target.matches('input, select, textarea')) {
				validateField(event.target, true);
			}
		});
		form.addEventListener('submit', (event) => {
			const invalidFields = Array.from(form.querySelectorAll('input, select, textarea'))
				.map((field) => validateField(field))
				.filter(Boolean);
			const uniqueErrors = Array.from(new Map(
				invalidFields.map((error) => [error.field.name || error.label, error]),
			).values());

			if (uniqueErrors.length) {
				event.preventDefault();
				showValidationSummary(uniqueErrors, 'Please check the form');
			}
		}, true);
	});
}

function getSidebarMenuPages() {
	const menu = document.querySelector('#sidebar-menu');

	if (!menu) {
		return [];
	}

	const seenUrls = new Set();

	return Array.from(menu.querySelectorAll('a[href]')).flatMap((link) => {
		const label = link.textContent.replace(/\s+/g, ' ').trim();
		const href = link.getAttribute('href');

		if (!label || !href || href === '#' || href.startsWith('javascript:')) {
			return [];
		}

		const url = new URL(href, window.location.href);

		if (url.origin !== window.location.origin || seenUrls.has(url.href)) {
			return [];
		}

		seenUrls.add(url.href);

		const sections = [];
		let parentMenuItem = link.closest('li')?.parentElement?.closest('li.submenu');

		while (parentMenuItem) {
			const sectionLabel = parentMenuItem.querySelector(':scope > a')
				?.textContent.replace(/\s+/g, ' ').trim();

			if (sectionLabel) {
				sections.push(sectionLabel);
			}

			parentMenuItem = parentMenuItem.parentElement?.closest('li.submenu');
		}

		return [{
			label,
			section: sections.reverse().join(' > '),
			url: url.href,
		}];
	});
}

function closeMenuSearch(search) {
	search.results.hidden = true;
	search.input.setAttribute('aria-expanded', 'false');
	search.input.removeAttribute('aria-activedescendant');
	search.activeIndex = -1;
}

function renderMenuSearch(search) {
	const query = search.input.value.trim().toLocaleLowerCase();
	search.results.replaceChildren();
	search.activeIndex = -1;

	if (!query) {
		closeMenuSearch(search);
		return;
	}

	const pages = getSidebarMenuPages();
	const matches = pages.filter((page) => {
		const searchableText = `${page.label} ${page.section}`.toLocaleLowerCase();
		return query.split(/\s+/).every((term) => searchableText.includes(term));
	});

	if (matches.length === 0) {
		const emptyState = document.createElement('div');
		emptyState.className = 'hrms-menu-search-empty';
		emptyState.textContent = 'No matching sidebar pages';
		search.results.append(emptyState);
		search.results.hidden = false;
		search.input.setAttribute('aria-expanded', 'true');
		return;
	}

	matches.forEach((page, index) => {
		const result = document.createElement('a');
		result.className = 'hrms-menu-search-result';
		result.href = page.url;
		result.id = `${search.id}-result-${index}`;
		result.setAttribute('role', 'option');
		result.setAttribute('aria-selected', 'false');

		const label = document.createElement('span');
		label.className = 'hrms-menu-search-result-label';
		label.textContent = page.label;
		result.append(label);

		if (page.section) {
			const section = document.createElement('small');
			section.className = 'hrms-menu-search-result-section';
			section.textContent = page.section;
			result.append(section);
		}

		search.results.append(result);
	});

	search.results.hidden = false;
	search.input.setAttribute('aria-expanded', 'true');
}

const menuSearches = Array.from(document.querySelectorAll('[data-search-shortcut]')).map((input, index) => {
	const search = {
		id: `hrms-menu-search-${index}`,
		input,
		results: input.closest('.hrms-menu-search').querySelector('[data-search-results]'),
		activeIndex: -1,
	};

	input.addEventListener('input', () => renderMenuSearch(search));
	input.addEventListener('keydown', (event) => {
		const options = Array.from(search.results.querySelectorAll('[role="option"]'));

		if (search.results.hidden || options.length === 0) {
			if (event.key === 'Escape') {
				closeMenuSearch(search);
			}
			return;
		}

		if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
			event.preventDefault();
			const direction = event.key === 'ArrowDown' ? 1 : -1;
			search.activeIndex = search.activeIndex === -1 && direction === -1
				? options.length - 1
				: (search.activeIndex + direction + options.length) % options.length;

			options.forEach((option, optionIndex) => {
				const isActive = optionIndex === search.activeIndex;
				option.setAttribute('aria-selected', String(isActive));
				option.classList.toggle('is-active', isActive);
			});

			const activeOption = options[search.activeIndex];
			input.setAttribute('aria-activedescendant', activeOption.id);
			activeOption.scrollIntoView({ block: 'nearest' });
		} else if (event.key === 'Enter' && search.activeIndex >= 0) {
			event.preventDefault();
			window.location.assign(options[search.activeIndex].href);
		} else if (event.key === 'Escape') {
			closeMenuSearch(search);
		}
	});
});

document.addEventListener('keydown', (event) => {
	if (!(event.ctrlKey || event.metaKey) || event.altKey) {
		return;
	}

	const key = event.key.toLowerCase();
	const isSearchShortcut = (key === '/' || event.code === 'Slash') || key === 's';

	if (!isSearchShortcut) {
		return;
	}

	const searchInput = Array.from(document.querySelectorAll('[data-search-shortcut]'))
		.find((input) => input.getClientRects().length > 0);

	if (!searchInput) {
		return;
	}

	event.preventDefault();
	searchInput.focus();
});

document.addEventListener('click', (event) => {
	menuSearches.forEach((search) => {
		if (!search.input.closest('.hrms-menu-search').contains(event.target)) {
			closeMenuSearch(search);
		}
	});
});

document.addEventListener('invalid', (event) => {
	const field = event.target;
	const error = getValidationError(field);

	if (!error) {
		return;
	}

	event.preventDefault();
	error.textContent = field.validationMessage;
	error.hidden = false;
	field.classList.add('is-invalid');
	field.setAttribute('aria-invalid', 'true');
}, true);

function clearValidationError(event) {
	const field = event.target;
	const error = getValidationError(field);

	if (!error || !field.validity.valid) {
		return;
	}

	error.textContent = '';
	error.hidden = true;
	field.classList.remove('is-invalid');
	field.removeAttribute('aria-invalid');
}

document.addEventListener('input', clearValidationError);
document.addEventListener('change', clearValidationError);

document.addEventListener('DOMContentLoaded', () => {
	if (typeof bootstrap === 'undefined') {
		return;
	}

	document.querySelectorAll('[data-validation-errors="true"]').forEach((modal) => {
		bootstrap.Modal.getOrCreateInstance(modal).show();
	});
});

document.addEventListener('submit', async (event) => {
	const form = event.target.closest('[data-ajax-pagination]');

	if (!form) {
		return;
	}

	event.preventDefault();

	const container = form.closest('[data-pagination-container]');
	const response = await fetch(form.action, {
		method: 'POST',
		headers: {
			Accept: 'application/json',
			'X-Requested-With': 'XMLHttpRequest',
			'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
		},
		body: new FormData(form),
	});

	if (!response.ok) {
		window.location.reload();
		return;
	}

	const payload = await response.json();
	container.outerHTML = payload.html;
});

document.addEventListener('click', (event) => {
	const link = event.target.closest('[data-ajax-pagination-links] a');

	if (!link) {
		return;
	}

	event.preventDefault();

	const container = link.closest('[data-pagination-container]');
	const form = container.querySelector('[data-ajax-pagination]');
	const url = new URL(link.href);
	const page = url.searchParams.get('page') ?? '1';

	form.querySelector('input[name="page"]').value = page;
	form.requestSubmit();
});

function renderNotificationList(items) {
	const list = document.querySelector('[data-notification-list]');

	if (!list) {
		return;
	}

	const viewAllLink = '<li><a href="/notifications" class="dropdown-item px-2 py-2 text-primary fw-medium">View all notifications</a></li>';

	if (!Array.isArray(items) || items.length === 0) {
		list.innerHTML = '<li><span class="dropdown-item px-2 py-2"><strong>Notifications</strong></span></li><li><span class="dropdown-item px-2 py-2 text-muted">No new notifications</span></li>' + viewAllLink;
		return;
	}

	const rows = items.map((item) => {
		const title = item.title || 'Notification';
		const message = item.message || '';
		const url = item.url && item.url !== '#' ? item.url : (item.detail_url || '#');
		const detailUrl = item.detail_url || '';
		const downloadUrl = item.download_url || '';
		const downloadButton = downloadUrl
			? `<a href="${downloadUrl}" class="btn btn-sm btn-primary mt-2">Download</a>`
			: '';
		const detailButton = detailUrl && detailUrl !== url
			? `<a href="${detailUrl}" class="btn btn-sm btn-light mt-2 ms-1">Details</a>`
			: '';

		return `<li><div class="dropdown-item px-2 py-2"><a href="${url}" class="d-block"><strong>${title}</strong><br><span class="text-muted">${message}</span></a>${downloadButton}${detailButton}</div></li>`;
	}).join('');

	list.innerHTML = '<li><span class="dropdown-item px-2 py-2"><strong>Notifications</strong></span></li>' + rows + viewAllLink;
}

function refreshNotifications() {
	const panel = document.querySelector('[data-notification-panel]');

	if (!panel) {
		return;
	}

	const url = panel.dataset.notificationPollUrl;

	if (!url) {
		return;
	}

	fetch(url, {
		headers: {
			Accept: 'application/json',
			'X-Requested-With': 'XMLHttpRequest',
		},
	})
		.then((response) => {
			if (!response.ok) {
				throw new Error('Notification poll failed');
			}

			return response.json();
		})
		.then((payload) => {
			const count = Number(payload.count || 0);
			const countNode = document.querySelector('[data-notification-count]');
			const importPage = document.querySelector('form[data-import-form="true"]');
			const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
			const completedImport = (payload.items || []).find((item) => {
				const notificationPath = new URL(item.url || '', window.location.origin).pathname.replace(/\/+$/, '') || '/';

				return item.type === 'import'
					&& notificationPath === currentPath;
			});

			if (completedImport?.id && !importPage) {
				const refreshKey = `import-refresh-${completedImport.id}`;

				if (!sessionStorage.getItem(refreshKey)) {
					sessionStorage.setItem(refreshKey, '1');
					window.location.reload();
					return;
				}
			}

			if (countNode) {
				countNode.textContent = String(count);
				countNode.hidden = count === 0;
			}

			if (count === 0 && !countNode) {
				return;
			}

			renderNotificationList(payload.items || []);
		})
		.catch(() => {
			// Ignore polling errors so the page remains usable.
		});
}

	function positionSidebarSubmenus() {
		document.querySelectorAll('#side-menu > li.dropdown').forEach((menuItem) => {
			const submenu = menuItem.querySelector(':scope > div');

			if (!submenu) {
				return;
			}

			const bounds = menuItem.getBoundingClientRect();
			submenu.style.setProperty('--sidebar-submenu-left', `${bounds.right}px`);
			submenu.style.setProperty('--sidebar-submenu-top', `${bounds.top}px`);
		});
	}

	document.addEventListener('DOMContentLoaded', () => {
		positionSidebarSubmenus();
		document.querySelectorAll('#side-menu > li.dropdown').forEach((menuItem) => {
			menuItem.addEventListener('mouseenter', positionSidebarSubmenus);
		});
	});

	window.addEventListener('resize', positionSidebarSubmenus);

	document.addEventListener('click', (event) => {
		const copyButton = event.target.closest('[data-copy-target]');

		if (copyButton) {
			const source = document.querySelector(copyButton.dataset.copyTarget);

			if (source) {
				copyText(source.value).then(() => {
					const originalText = copyButton.textContent;
					copyButton.textContent = 'Copied';
					window.setTimeout(() => {
						copyButton.textContent = originalText;
					}, 1500);
				});
			}

			return;
		}

		const dropdownLink = event.target.closest('#side-menu > li.dropdown > a');

		if (!dropdownLink) {
			return;
		}

		event.preventDefault();
	});

	async function copyText(text) {
		if (navigator.clipboard && window.isSecureContext) {
			try {
				await navigator.clipboard.writeText(text);
				return;
			} catch {
				// Fall back when clipboard permissions are denied.
			}
		}

		const fallbackInput = document.createElement('textarea');
		fallbackInput.value = text;
		fallbackInput.setAttribute('readonly', '');
		fallbackInput.style.position = 'fixed';
		fallbackInput.style.opacity = '0';
		document.body.appendChild(fallbackInput);
		fallbackInput.select();
		document.execCommand('copy');
		fallbackInput.remove();
	}

document.addEventListener('DOMContentLoaded', () => {
	initializeFormValidation();

	const panel = document.querySelector('[data-notification-panel]');

	if (!panel) {
		return;
	}

	refreshNotifications();

	const refreshInterval = Number(panel.dataset.refreshInterval || 15000);
	window.setInterval(refreshNotifications, refreshInterval);

	window.addEventListener('pageshow', refreshNotifications);
	document.addEventListener('visibilitychange', () => {
		if (document.visibilityState === 'visible') {
			refreshNotifications();
		}
	});
});
