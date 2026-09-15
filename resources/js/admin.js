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
			const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
			const completedImport = (payload.items || []).find((item) => {
				const notificationPath = new URL(item.url || '', window.location.origin).pathname.replace(/\/+$/, '') || '/';

				return item.type === 'import'
					&& notificationPath === currentPath;
			});

			if (completedImport?.id) {
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
	const panel = document.querySelector('[data-notification-panel]');

	if (!panel) {
		return;
	}

	refreshNotifications();

	const refreshInterval = Number(panel.dataset.refreshInterval || 15000);
	window.setInterval(refreshNotifications, refreshInterval);
});
