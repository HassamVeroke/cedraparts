/**
 * Cedra Parts — Interactive Frontend Logic (Vanilla JS)
 *
 * - Hero cursor spotlight
 * - Live America/Toronto (ET) Open/Closed indicator
 * - Instant AJAX Catalog (Category, Brand, SKU/Keyword Search, Sort, Load More, History sync)
 * - AJAX Add-to-Cart & Slide-Over Frosted-Glass Mini-Cart Drawer
 * - Single Product gallery switcher & quantity stepper
 * - Mobile glass menu toggle
 */
(function () {
	'use strict';

	const cfg = window.CedraConfig || {
		ajaxUrl: '/wp-admin/admin-ajax.php',
		nonce: '',
		shopUrl: '/',
		spotlight: true,
		perPage: 12
	};

	const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* --------------------------------------------------------------------------
	 * 1. Hero Cursor Spotlight
	 * -------------------------------------------------------------------------- */
	function initSpotlight() {
		if (!cfg.spotlight || prefersReducedMotion) {
			return;
		}
		const hero = document.querySelector('.js-hero-section');
		const spot = document.querySelector('.js-hero-spotlight');
		if (!hero || !spot) {
			return;
		}

		let rafId = 0;
		let targetX = 900;
		let targetY = 220;

		hero.addEventListener('mousemove', function (e) {
			const rect = hero.getBoundingClientRect();
			targetX = Math.round(e.clientX - rect.left);
			targetY = Math.round(e.clientY - rect.top);
			spot.classList.add('is-visible');

			if (!rafId) {
				rafId = window.requestAnimationFrame(function () {
					spot.style.left = targetX + 'px';
					spot.style.top = targetY + 'px';
					rafId = 0;
				});
			}
		});

		hero.addEventListener('mouseleave', function () {
			spot.classList.remove('is-visible');
		});
	}

	/* --------------------------------------------------------------------------
	 * 2. Live Eastern Time (America/Toronto) Open / Closed Indicator
	 * -------------------------------------------------------------------------- */
	function computeTorontoOpen() {
		try {
			const parts = new Intl.DateTimeFormat('en-CA', {
				timeZone: 'America/Toronto',
				weekday: 'short',
				hour: 'numeric',
				hour12: false
			}).formatToParts(new Date());

			const wdPart = parts.find(function (p) { return p.type === 'weekday'; });
			const hrPart = parts.find(function (p) { return p.type === 'hour'; });
			const wd = wdPart ? wdPart.value : '';
			const hr = hrPart ? parseInt(hrPart.value, 10) : -1;

			return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].indexOf(wd) !== -1 && hr >= 9 && hr < 17;
		} catch (err) {
			return false;
		}
	}

	function updateEtStatus() {
		const dot = document.querySelector('.js-et-status-dot');
		const label = document.querySelector('.js-et-status-label');
		if (!dot || !label) {
			return;
		}
		const open = computeTorontoOpen();
		dot.style.background = open ? '#5BD69A' : '#F0A35E';
		label.textContent = open ? 'Open now' : 'Closed now';
	}

	/* --------------------------------------------------------------------------
	 * 3. Mobile Navigation Toggle
	 * -------------------------------------------------------------------------- */
	function initMobileMenu() {
		const btn = document.querySelector('.js-toggle-mobile-menu');
		const menu = document.getElementById('cedra-mobile-menu');
		if (!btn || !menu) {
			return;
		}

		btn.addEventListener('click', function () {
			const isOpen = !menu.hasAttribute('hidden');
			if (isOpen) {
				menu.setAttribute('hidden', '');
				btn.setAttribute('aria-expanded', 'false');
			} else {
				menu.removeAttribute('hidden');
				btn.setAttribute('aria-expanded', 'true');
			}
		});
	}

	/* --------------------------------------------------------------------------
	 * 4. Slide-Over Frosted-Glass Mini-Cart Drawer & AJAX Cart Actions
	 * -------------------------------------------------------------------------- */
	const drawer = document.getElementById('cedra-cart-drawer');
	const drawerBody = document.querySelector('.js-cedra-drawer-body');

	function openCartDrawer() {
		if (!drawer) {
			return;
		}
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
	}

	function closeCartDrawer() {
		if (!drawer) {
			return;
		}
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
	}

	function updateCartUi(count, html) {
		document.querySelectorAll('.js-cedra-cart-count').forEach(function (el) {
			el.textContent = '(' + count + ')';
		});
		const navCart = document.querySelector('.cedra-nav__cart');
		if (navCart) {
			navCart.setAttribute('aria-label', 'Cart, ' + count + ' items');
		}
		if (drawerBody && typeof html === 'string') {
			drawerBody.innerHTML = html;
		}
	}

	function sendCartRequest(payload) {
		const body = new URLSearchParams();
		body.set('action', 'cedra_cart_action');
		body.set('nonce', cfg.nonce);
		Object.keys(payload).forEach(function (k) {
			body.set(k, String(payload[k]));
		});

		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (r) { return r.json(); });
	}

	function initCartInteractions() {
		// Navbar cart trigger
		document.querySelectorAll('.js-open-cart-drawer').forEach(function (link) {
			link.addEventListener('click', function (e) {
				if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
					return;
				}
				// On cart or checkout page itself, let native link work or do nothing
				if (document.body.classList.contains('woocommerce-cart') || document.body.classList.contains('woocommerce-checkout')) {
					return;
				}
				if (drawer) {
					e.preventDefault();
					openCartDrawer();
				}
			});
		});

		// Delegated clicks for drawer close, drawer qty updates, & product card "+" buttons
		document.addEventListener('click', function (e) {
			const closeBtn = e.target.closest('.js-close-cart-drawer');
			if (closeBtn) {
				closeCartDrawer();
				return;
			}

			const qtyBtn = e.target.closest('.js-drawer-qty');
			if (qtyBtn) {
				e.preventDefault();
				const cartKey = qtyBtn.getAttribute('data-cart-key');
				const nextQty = parseInt(qtyBtn.getAttribute('data-qty') || '0', 10);
				if (!cartKey) {
					return;
				}
				qtyBtn.disabled = true;
				sendCartRequest({ op: 'update', cart_key: cartKey, quantity: nextQty })
					.then(function (res) {
						if (res && res.success && res.data) {
							updateCartUi(res.data.count, res.data.drawerHtml);
						}
					})
					.finally(function () {
						qtyBtn.disabled = false;
					});
				return;
			}

			const addBtn = e.target.closest('.js-cedra-add-to-cart');
			if (addBtn) {
				e.preventDefault();
				const productId = addBtn.getAttribute('data-product-id');
				if (!productId || addBtn.classList.contains('is-busy')) {
					return;
				}
				addBtn.classList.add('is-busy');

				sendCartRequest({ op: 'add', product_id: productId, quantity: 1 })
					.then(function (res) {
						if (res && res.success && res.data) {
							updateCartUi(res.data.count, res.data.drawerHtml);
							addBtn.classList.add('is-added');
							openCartDrawer();
							window.setTimeout(function () {
								addBtn.classList.remove('is-added');
							}, 1800);
						} else if (res && res.data && res.data.redirect) {
							window.location.href = res.data.redirect;
						}
					})
					.catch(function () {
						window.location.href = '?add-to-cart=' + encodeURIComponent(productId);
					})
					.finally(function () {
						addBtn.classList.remove('is-busy');
					});
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
				closeCartDrawer();
			}
		});
	}

	/* --------------------------------------------------------------------------
	 * 5. Instant AJAX Catalog Filtering, Search, Sorting & Load More
	 * -------------------------------------------------------------------------- */
	function initCatalog() {
		const root = document.querySelector('.js-catalog-root');
		if (!root) {
			return;
		}

		const grid = root.querySelector('.js-catalog-grid');
		const titleEl = root.querySelector('.js-catalog-title');
		const countEl = root.querySelector('.js-catalog-count');
		const pagePill = root.querySelector('.js-catalog-page-pill');
		const shownEl = root.querySelector('.js-catalog-shown');
		const totalEl = root.querySelector('.js-catalog-total');
		const progressEl = root.querySelector('.js-catalog-progress');
		const loadMoreBtn = root.querySelector('.js-catalog-loadmore');
		const footerWrap = root.querySelector('.js-catalog-footer');
		const clearHeaderBtn = root.querySelector('.cedra-clear-pill.js-cedra-reset-filters');

		const searchForm = document.querySelector('.js-catalog-search-form');
		const qInput = document.querySelector('.js-catalog-q');
		const brandSelect = document.querySelector('.js-catalog-brand');
		const sortSelect = document.querySelector('.js-catalog-sort');
		const catButtons = Array.prototype.slice.call(document.querySelectorAll('.js-catalog-cat'));

		const state = {
			cat: root.getAttribute('data-cat') || 'all',
			brand: root.getAttribute('data-brand') || 'all',
			q: root.getAttribute('data-q') || '',
			sort: root.getAttribute('data-sort') || 'featured',
			page: parseInt(root.getAttribute('data-page') || '1', 10)
		};

		let activeAbort = null;

		function syncControlsFromState() {
			catButtons.forEach(function (btn) {
				const btnCat = btn.getAttribute('data-cat') || 'all';
				const selected = btnCat === state.cat;
				btn.classList.toggle('is-selected', selected);
				btn.classList.toggle('pill', !selected);
				btn.setAttribute('aria-pressed', selected ? 'true' : 'false');
			});

			if (qInput && qInput.value !== state.q) {
				qInput.value = state.q;
			}
			if (brandSelect && brandSelect.value !== state.brand) {
				brandSelect.value = state.brand;
			}
			if (sortSelect && sortSelect.value !== state.sort) {
				sortSelect.value = state.sort;
			}
		}

		function pushBrowserUrl() {
			if (!window.history || !window.history.pushState) {
				return;
			}
			const url = new URL(cfg.shopUrl, window.location.origin);
			if (state.cat && state.cat !== 'all') {
				url.searchParams.set('cat', state.cat);
			}
			if (state.brand && state.brand !== 'all') {
				url.searchParams.set('brand', state.brand);
			}
			if (state.q) {
				url.searchParams.set('q', state.q);
			}
			if (state.sort && state.sort !== 'featured') {
				url.searchParams.set('orderby', state.sort);
			}
			window.history.pushState({ ...state }, '', url.toString());
		}

		function fetchCatalog(append, updateHistory) {
			if (activeAbort) {
				activeAbort.abort();
			}
			activeAbort = new AbortController();

			if (append && loadMoreBtn) {
				loadMoreBtn.classList.add('is-busy');
				loadMoreBtn.textContent = 'Loading parts…';
			} else if (grid) {
				grid.classList.add('is-loading');
			}

			const body = new URLSearchParams();
			body.set('action', 'cedra_filter_catalog');
			body.set('nonce', cfg.nonce);
			body.set('cat', state.cat);
			body.set('brand', state.brand);
			body.set('q', state.q);
			body.set('sort', state.sort);
			body.set('page', String(state.page));
			body.set('append', append ? 'true' : 'false');

			fetch(cfg.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				signal: activeAbort.signal,
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			})
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res || !res.success || !res.data) {
						return;
					}
					const d = res.data;

					if (grid) {
						if (append) {
							grid.insertAdjacentHTML('beforeend', d.html);
						} else {
							grid.innerHTML = d.html;
						}
					}

					if (titleEl) {
						titleEl.textContent = d.catTitle;
					}
					if (countEl) {
						countEl.textContent = d.foundFormatted;
					}
					if (pagePill) {
						pagePill.textContent = d.pageLabel;
					}
					if (shownEl) {
						shownEl.textContent = d.shownFormatted;
					}
					if (totalEl) {
						totalEl.textContent = d.foundFormatted;
					}
					if (progressEl) {
						progressEl.style.width = d.progressPct + '%';
					}
					if (footerWrap) {
						footerWrap.hidden = d.found === 0;
					}
					if (loadMoreBtn) {
						loadMoreBtn.hidden = !d.hasMore;
						loadMoreBtn.textContent = 'Load more parts';
					}
					if (clearHeaderBtn) {
						clearHeaderBtn.hidden = !d.hasFilters;
					}

					if (updateHistory) {
						pushBrowserUrl();
					}
				})
				.catch(function (err) {
					if (err && err.name === 'AbortError') {
						return;
					}
				})
				.finally(function () {
					if (grid) {
						grid.classList.remove('is-loading');
					}
					if (loadMoreBtn) {
						loadMoreBtn.classList.remove('is-busy');
						if (loadMoreBtn.textContent !== 'Load more parts') {
							loadMoreBtn.textContent = 'Load more parts';
						}
					}
				});
		}

		// Category pill clicks
		catButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				const picked = btn.getAttribute('data-cat') || 'all';
				if (picked === state.cat && state.page === 1) {
					return;
				}
				state.cat = picked;
				state.page = 1;
				syncControlsFromState();
				fetchCatalog(false, true);
			});
		});

		// Sort select
		if (sortSelect) {
			sortSelect.addEventListener('change', function () {
				state.sort = sortSelect.value || 'featured';
				state.page = 1;
				fetchCatalog(false, true);
			});
		}

		// Brand select
		if (brandSelect) {
			brandSelect.addEventListener('change', function () {
				state.brand = brandSelect.value || 'all';
				state.page = 1;
				fetchCatalog(false, true);
			});
		}

		// Search form submit
		if (searchForm) {
			searchForm.addEventListener('submit', function (e) {
				e.preventDefault();
				state.q = qInput ? qInput.value.trim() : '';
				if (brandSelect) {
					state.brand = brandSelect.value || 'all';
				}
				state.page = 1;
				fetchCatalog(false, true);

				// Scroll catalog into view if below fold
				const catalogRect = root.getBoundingClientRect();
				if (catalogRect.top > window.innerHeight * 0.75) {
					root.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
				}
			});
		}

		// Native search input "clear" (x) button
		if (qInput) {
			qInput.addEventListener('search', function () {
				if (qInput.value.trim() === '' && state.q !== '') {
					state.q = '';
					state.page = 1;
					fetchCatalog(false, true);
				}
			});
		}

		// Load more button
		if (loadMoreBtn) {
			loadMoreBtn.addEventListener('click', function () {
				state.page += 1;
				fetchCatalog(true, false);
			});
		}

		// Reset filters buttons (delegated for both header pill & empty-state card)
		document.addEventListener('click', function (e) {
			const resetBtn = e.target.closest('.js-cedra-reset-filters');
			if (!resetBtn) {
				return;
			}
			e.preventDefault();
			state.cat = 'all';
			state.brand = 'all';
			state.q = '';
			state.sort = 'featured';
			state.page = 1;
			syncControlsFromState();
			fetchCatalog(false, true);
		});

		// Browser Back / Forward navigation
		window.addEventListener('popstate', function (e) {
			if (e.state && typeof e.state.cat === 'string') {
				state.cat = e.state.cat;
				state.brand = e.state.brand || 'all';
				state.q = e.state.q || '';
				state.sort = e.state.sort || 'featured';
				state.page = 1;
			} else {
				const params = new URLSearchParams(window.location.search);
				state.cat = params.get('cat') || 'all';
				state.brand = params.get('brand') || 'all';
				state.q = params.get('q') || '';
				state.sort = params.get('orderby') || 'featured';
				state.page = 1;
			}
			syncControlsFromState();
			fetchCatalog(false, false);
		});
	}

	/* --------------------------------------------------------------------------
	 * 6. Single Product Page Interactions (Gallery Thumbs, Qty Stepper, AJAX Add)
	 * -------------------------------------------------------------------------- */
	function initSingleProduct() {
		const mainImg = document.querySelector('.js-pdp-main-img');
		const thumbs = document.querySelectorAll('.js-pdp-thumb');
		if (mainImg && thumbs.length > 0) {
			thumbs.forEach(function (thumb) {
				thumb.addEventListener('click', function () {
					const fullSrc = thumb.getAttribute('data-full-src');
					const srcset = thumb.getAttribute('data-srcset');
					if (fullSrc) {
						mainImg.src = fullSrc;
						if (srcset) {
							mainImg.srcset = srcset;
						} else {
							mainImg.removeAttribute('srcset');
						}
					}
					thumbs.forEach(function (t) { t.classList.remove('is-active'); });
					thumb.classList.add('is-active');
				});
			});
		}

		// Quantity stepper
		const qtyInput = document.querySelector('.js-pdp-qty-input');
		document.querySelectorAll('.js-pdp-qty-step').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!qtyInput) {
					return;
				}
				const step = parseInt(btn.getAttribute('data-step') || '1', 10);
				const min = parseInt(qtyInput.getAttribute('min') || '1', 10);
				const maxAttr = qtyInput.getAttribute('max');
				const max = maxAttr ? parseInt(maxAttr, 10) : Infinity;
				const current = parseInt(qtyInput.value || '1', 10) || 1;
				const next = Math.min(max, Math.max(min, current + step));
				qtyInput.value = String(next);
			});
		});

		// AJAX Add to Cart on Single Product
		const pdpForm = document.querySelector('.js-pdp-cart-form');
		const pdpBtn = document.querySelector('.js-pdp-ajax-add');
		if (pdpForm && pdpBtn) {
			pdpForm.addEventListener('submit', function (e) {
				const productId = pdpBtn.getAttribute('data-product-id');
				if (!productId || !drawer) {
					return;
				}
				e.preventDefault();
				const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value || '1', 10) || 1) : 1;
				pdpBtn.disabled = true;

				sendCartRequest({ op: 'add', product_id: productId, quantity: qty })
					.then(function (res) {
						if (res && res.success && res.data) {
							updateCartUi(res.data.count, res.data.drawerHtml);
							openCartDrawer();
						} else {
							pdpForm.submit();
						}
					})
					.catch(function () {
						pdpForm.submit();
					})
					.finally(function () {
						pdpBtn.disabled = false;
					});
			});
		}
	}

	/* --------------------------------------------------------------------------
	 * Boot
	 * -------------------------------------------------------------------------- */
	document.addEventListener('DOMContentLoaded', function () {
		initSpotlight();
		updateEtStatus();
		window.setInterval(updateEtStatus, 60000);
		initMobileMenu();
		initCartInteractions();
		initCatalog();
		initSingleProduct();
	});
})();
