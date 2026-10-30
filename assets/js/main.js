/**
 * Cedra Parts — Option 2 Interactive Frontend Controller
 *
 * Features:
 * 1. 4-Slide Hero Carousel (7s autoplay, hover/focus pause, touch swipe, adaptive dark/light controls)
 * 2. Slide 2 OEM Part Cross-Reference Form & "Other brands" CTA
 * 3. Shop by Categories / Brands Tab Switcher & Tile Click-to-Filter
 * 4. Multi-Facet Live AJAX Catalog Filtering (Category, Brand, Price, DC Voltage, Search, Sort, Chips & Pagination)
 * 5. Mobile Slide-In Filters Drawer & Collapsible Header Menu
 * 6. Slide-Over Mini-Cart Drawer, AJAX Add-to-Cart & Single Product Gallery/Quantity Controls
 *
 * @package CedraParts
 */

(function () {
	'use strict';

	var cfg = window.CedraConfig || {
		ajaxUrl: '/wp-admin/admin-ajax.php',
		nonce: '',
		shopUrl: '/',
		quoteUrl: '/#quote',
		autoplay: true,
		perPage: 12
	};

	var prefersReducedMotion = false;
	try {
		prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	} catch (e) {
		prefersReducedMotion = false;
	}

	function scrollToElement(el) {
		if (!el) return;
		var headerOffset = 84;
		var rect = el.getBoundingClientRect();
		var top = rect.top + window.pageYOffset - headerOffset;
		window.scrollTo({
			top: Math.max(0, top),
			behavior: prefersReducedMotion ? 'auto' : 'smooth'
		});
	}

	/* ======================================================================
	   1. Mobile Header Navigation & Header Search Focus
	   ====================================================================== */

	var mobileNavBtn = document.querySelector('.js-mobile-nav-toggle');
	var mobileMenu = document.getElementById('cedra-mobile-menu');

	if (mobileNavBtn && mobileMenu) {
		mobileNavBtn.addEventListener('click', function () {
			var isOpen = !mobileMenu.hasAttribute('hidden');
			if (isOpen) {
				mobileMenu.setAttribute('hidden', '');
				mobileNavBtn.setAttribute('aria-expanded', 'false');
			} else {
				mobileMenu.removeAttribute('hidden');
				mobileNavBtn.setAttribute('aria-expanded', 'true');
			}
		});
	}

	document.querySelectorAll('.js-focus-search').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			var resultsEl = document.getElementById('results');
			var searchInput = document.getElementById('q');
			if (resultsEl && searchInput) {
				e.preventDefault();
				scrollToElement(resultsEl);
				setTimeout(function () {
					searchInput.focus({ preventScroll: true });
				}, 250);
			}
		});
	});

	/* ======================================================================
	   2. 4-Slide Hero Carousel
	   ====================================================================== */

	var heroCarousel = document.querySelector('.js-hero-carousel');
	var goToHeroSlide = null;

	if (heroCarousel) {
		var track = heroCarousel.querySelector('.js-carousel-track');
		var dots = Array.prototype.slice.call(heroCarousel.querySelectorAll('.js-carousel-dot'));
		var counter = heroCarousel.querySelector('.js-carousel-counter');
		var prevBtn = heroCarousel.querySelector('.js-carousel-prev');
		var nextBtn = heroCarousel.querySelector('.js-carousel-next');
		var slideCount = 4;
		var currentSlide = 0;
		var isPaused = false;
		var touchStartX = null;

		goToHeroSlide = function (idx) {
			currentSlide = ((idx % slideCount) + slideCount) % slideCount;
			if (track) {
				track.style.transform = 'translateX(' + (-currentSlide * 100) + '%)';
			}
			heroCarousel.setAttribute('data-dark', currentSlide === 0 ? 'true' : 'false');

			dots.forEach(function (dot, i) {
				var active = i === currentSlide;
				dot.classList.toggle('is-active', active);
				dot.setAttribute('aria-current', active ? 'true' : 'false');
			});

			if (counter) {
				counter.textContent = '0' + (currentSlide + 1) + ' / 0' + slideCount;
			}
		};

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var target = parseInt(dot.getAttribute('data-slide') || '0', 10);
				goToHeroSlide(target);
			});
		});

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				goToHeroSlide(currentSlide - 1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				goToHeroSlide(currentSlide + 1);
			});
		}

		heroCarousel.addEventListener('mouseenter', function () { isPaused = true; });
		heroCarousel.addEventListener('mouseleave', function () { isPaused = false; });
		heroCarousel.addEventListener('focusin', function () { isPaused = true; });
		heroCarousel.addEventListener('focusout', function () { isPaused = false; });

		heroCarousel.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft' && document.activeElement.tagName !== 'INPUT') {
				goToHeroSlide(currentSlide - 1);
			} else if (e.key === 'ArrowRight' && document.activeElement.tagName !== 'INPUT') {
				goToHeroSlide(currentSlide + 1);
			}
		});

		heroCarousel.addEventListener('touchstart', function (e) {
			touchStartX = e.touches && e.touches[0] ? e.touches[0].clientX : null;
		}, { passive: true });

		heroCarousel.addEventListener('touchend', function (e) {
			if (touchStartX === null) return;
			var endX = e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientX : touchStartX;
			var dx = endX - touchStartX;
			touchStartX = null;
			if (Math.abs(dx) > 40) {
				goToHeroSlide(currentSlide + (dx < 0 ? 1 : -1));
			}
		}, { passive: true });

		if (cfg.autoplay && !prefersReducedMotion) {
			setInterval(function () {
				if (!isPaused && !document.hidden) {
					goToHeroSlide(currentSlide + 1);
				}
			}, 7000);
		}
	}

	/* ======================================================================
	   3. Shop Tabs (Categories / Brands)
	   ====================================================================== */

	var shopSection = document.getElementById('shop');
	var shopTabs = Array.prototype.slice.call(document.querySelectorAll('.js-shop-tab'));
	var shopPanels = Array.prototype.slice.call(document.querySelectorAll('.js-shop-panel'));

	function switchShopTab(tabId) {
		shopTabs.forEach(function (btn) {
			var active = btn.getAttribute('data-tab') === tabId;
			btn.classList.toggle('is-active', active);
			btn.setAttribute('aria-selected', active ? 'true' : 'false');
		});
		shopPanels.forEach(function (panel) {
			var match = panel.getAttribute('data-panel') === tabId;
			if (match) {
				panel.removeAttribute('hidden');
			} else {
				panel.setAttribute('hidden', '');
			}
		});
	}

	shopTabs.forEach(function (btn) {
		btn.addEventListener('click', function () {
			switchShopTab(btn.getAttribute('data-tab') || 'cat');
		});
	});

	document.querySelectorAll('.js-show-brands').forEach(function (link) {
		link.addEventListener('click', function (e) {
			if (shopSection && shopTabs.length) {
				e.preventDefault();
				switchShopTab('brand');
				scrollToElement(shopSection);
			}
		});
	});

	/* ======================================================================
	   4. Multi-Facet Live AJAX Catalog Filtering & Mobile Filters Drawer
	   ====================================================================== */

	var resultsSection = document.getElementById('results');
	var resultsMain = document.querySelector('.cedra-results-main');
	var resultsContainer = document.querySelector('.js-results-container');
	var paginationContainer = document.querySelector('.js-pagination-container');
	var chipsContainer = document.querySelector('.js-chips-container');
	var searchInput = document.querySelector('.js-catalog-search');
	var clearSearchBtn = document.querySelector('.js-clear-search');
	var sortSelect = document.querySelector('.js-catalog-sort');
	var activeCountBadge = document.querySelector('.js-active-count');
	var filtersDrawer = document.querySelector('.js-filters-drawer');
	var filtersScrim = document.querySelector('.scrim');

	var activeAbort = null;
	var searchDebounce = null;

	function setFiltersDrawerOpen(open) {
		if (!filtersDrawer || !filtersScrim) return;
		filtersDrawer.classList.toggle('open', open);
		filtersScrim.classList.toggle('open', open);
		document.body.style.overflow = open ? 'hidden' : '';
	}

	document.querySelectorAll('.js-open-filters').forEach(function (btn) {
		btn.addEventListener('click', function () {
			setFiltersDrawerOpen(true);
		});
	});

	document.querySelectorAll('.js-close-filters').forEach(function (btn) {
		btn.addEventListener('click', function () {
			setFiltersDrawerOpen(false);
		});
	});

	function getCatalogStateFromDOM(pageOverride) {
		var cats = [];
		document.querySelectorAll('.js-filter-cat:checked').forEach(function (cb) {
			cats.push(cb.value);
		});

		var brands = [];
		document.querySelectorAll('.js-filter-brand:checked').forEach(function (cb) {
			brands.push(cb.value);
		});

		var priceEl = document.querySelector('.js-filter-price:checked');
		var price = priceEl ? priceEl.value : 'any';

		var volts = [];
		document.querySelectorAll('.js-filter-volt.is-pressed').forEach(function (vb) {
			var v = vb.getAttribute('data-volt');
			if (v) volts.push(v);
		});

		var q = searchInput ? searchInput.value.trim() : '';
		var sort = sortSelect ? sortSelect.value : 'featured';

		return {
			cats: cats,
			brands: brands,
			volts: volts,
			price: price,
			q: q,
			sort: sort,
			page: pageOverride || 1
		};
	}

	function syncSearchClearButton() {
		if (!searchInput || !clearSearchBtn) return;
		if (searchInput.value.trim().length > 0) {
			clearSearchBtn.removeAttribute('hidden');
		} else {
			clearSearchBtn.setAttribute('hidden', '');
		}
	}

	function setCatalogStateInDOM(next) {
		if (Array.isArray(next.cats)) {
			document.querySelectorAll('.js-filter-cat').forEach(function (cb) {
				cb.checked = next.cats.indexOf(cb.value) !== -1;
			});
		}
		if (Array.isArray(next.brands)) {
			document.querySelectorAll('.js-filter-brand').forEach(function (cb) {
				cb.checked = next.brands.indexOf(cb.value) !== -1;
			});
		}
		if (typeof next.price === 'string') {
			document.querySelectorAll('.js-filter-price').forEach(function (rb) {
				rb.checked = rb.value === next.price;
			});
		}
		if (Array.isArray(next.volts)) {
			document.querySelectorAll('.js-filter-volt').forEach(function (vb) {
				var on = next.volts.indexOf(vb.getAttribute('data-volt')) !== -1;
				vb.classList.toggle('is-pressed', on);
				vb.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		}
		if (typeof next.q === 'string' && searchInput) {
			searchInput.value = next.q;
			syncSearchClearButton();
		}
		if (typeof next.sort === 'string' && sortSelect) {
			sortSelect.value = next.sort;
		}
	}

	function updateBrowserUrl(state, replaceOnly) {
		if (!window.history || !window.history.pushState) return;
		try {
			var url = new URL(cfg.shopUrl || window.location.origin + '/', window.location.origin);
			if (state.cats.length) url.searchParams.set('cats', state.cats.join(','));
			if (state.brands.length) url.searchParams.set('brands', state.brands.join(','));
			if (state.price && state.price !== 'any') url.searchParams.set('price', state.price);
			if (state.volts.length) url.searchParams.set('volts', state.volts.join(','));
			if (state.q) url.searchParams.set('q', state.q);
			if (state.sort && state.sort !== 'featured') url.searchParams.set('sort', state.sort);
			if (state.page && state.page > 1) url.searchParams.set('cpage', String(state.page));

			var nextHref = url.pathname + url.search + (window.location.hash || '');
			if (replaceOnly) {
				window.history.replaceState(state, '', nextHref);
			} else {
				window.history.pushState(state, '', nextHref);
			}
		} catch (e) {
			// Ignore URL construction errors on older browsers.
		}
	}

	function fetchCatalog(opts) {
		if (!resultsContainer) return;
		var options = opts || {};
		var state = getCatalogStateFromDOM(options.page || 1);

		if (options.updateUrl !== false) {
			updateBrowserUrl(state, !!options.replaceUrl);
		}

		if (activeAbort) {
			activeAbort.abort();
		}
		activeAbort = typeof AbortController !== 'undefined' ? new AbortController() : null;

		if (resultsMain) {
			resultsMain.classList.add('is-loading');
		}

		var body = new URLSearchParams();
		body.set('action', 'cedra_filter_catalog');
		body.set('nonce', cfg.nonce);
		body.set('cats', state.cats.join(','));
		body.set('brands', state.brands.join(','));
		body.set('volts', state.volts.join(','));
		body.set('price', state.price);
		body.set('q', state.q);
		body.set('sort', state.sort);
		body.set('page', String(state.page));

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
			signal: activeAbort ? activeAbort.signal : undefined
		})
			.then(function (res) { return res.json(); })
			.then(function (payload) {
				if (resultsMain) {
					resultsMain.classList.remove('is-loading');
				}
				if (!payload || !payload.success || !payload.data) return;

				var d = payload.data;
				resultsContainer.innerHTML = d.resultsHtml || '';
				if (paginationContainer) {
					paginationContainer.innerHTML = d.paginationHtml || '';
				}
				if (chipsContainer) {
					chipsContainer.innerHTML = d.chipsHtml || '';
				}

				document.querySelectorAll('.js-result-count').forEach(function (el) {
					el.textContent = d.foundFormatted || String(d.found || 0);
				});
				document.querySelectorAll('.js-result-noun').forEach(function (el) {
					el.textContent = d.resultNoun || 'results';
				});

				if (activeCountBadge) {
					var ac = parseInt(d.activeCount || 0, 10);
					activeCountBadge.textContent = String(ac);
					if (ac > 0) {
						activeCountBadge.removeAttribute('hidden');
					} else {
						activeCountBadge.setAttribute('hidden', '');
					}
				}

				if (options.scrollToResults && resultsSection) {
					scrollToElement(resultsSection);
				}
			})
			.catch(function (err) {
				if (err && err.name === 'AbortError') return;
				if (resultsMain) {
					resultsMain.classList.remove('is-loading');
				}
			});
	}

	if (resultsSection && resultsContainer) {
		document.querySelectorAll('.js-filter-cat, .js-filter-brand, .js-filter-price').forEach(function (input) {
			input.addEventListener('change', function () {
				fetchCatalog({ page: 1 });
			});
		});

		document.querySelectorAll('.js-filter-volt').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var nextOn = !btn.classList.contains('is-pressed');
				btn.classList.toggle('is-pressed', nextOn);
				btn.setAttribute('aria-pressed', nextOn ? 'true' : 'false');
				fetchCatalog({ page: 1 });
			});
		});

		if (searchInput) {
			searchInput.addEventListener('input', function () {
				syncSearchClearButton();
				if (searchDebounce) clearTimeout(searchDebounce);
				searchDebounce = setTimeout(function () {
					fetchCatalog({ page: 1, replaceUrl: true });
				}, 260);
			});
		}

		if (clearSearchBtn && searchInput) {
			clearSearchBtn.addEventListener('click', function () {
				searchInput.value = '';
				syncSearchClearButton();
				searchInput.focus();
				fetchCatalog({ page: 1 });
			});
		}

		if (sortSelect) {
			sortSelect.addEventListener('change', function () {
				fetchCatalog({ page: 1 });
			});
		}

		// Category tiles, "View all X parts", and footer category links.
		document.querySelectorAll('.js-pick-cat').forEach(function (tile) {
			tile.addEventListener('click', function (e) {
				e.preventDefault();
				var catSlug = tile.getAttribute('data-cat') || 'all';
				setCatalogStateInDOM({
					cats: catSlug === 'all' ? [] : [catSlug],
					brands: [],
					q: ''
				});
				fetchCatalog({ page: 1, scrollToResults: true });
			});
		});

		// Brand tiles in #shop and distributor logo tiles in Slide 4.
		document.querySelectorAll('.js-pick-brand').forEach(function (tile) {
			tile.addEventListener('click', function (e) {
				e.preventDefault();
				var brandSlug = tile.getAttribute('data-brand') || '';
				if (!brandSlug) return;
				setCatalogStateInDOM({
					cats: [],
					brands: [brandSlug],
					q: ''
				});
				fetchCatalog({ page: 1, scrollToResults: true });
			});
		});

		// Slide 2 OEM Part Cross-Reference Form.
		document.querySelectorAll('.js-xref-form').forEach(function (form) {
			form.addEventListener('submit', function (e) {
				var input = form.querySelector('.js-xref-input');
				var val = input ? input.value.trim() : '';
				if (val.length > 0) {
					e.preventDefault();
					setCatalogStateInDOM({
						cats: [],
						brands: [],
						volts: [],
						price: 'any',
						q: val
					});
					fetchCatalog({ page: 1, scrollToResults: true });
				}
			});
		});

		// "Other brands — Cross-reference any part" tile in Brands tab.
		document.querySelectorAll('.js-xref-cta').forEach(function (cta) {
			cta.addEventListener('click', function (e) {
				var heroTop = document.getElementById('top');
				var xrefInput = document.getElementById('xref');
				if (heroTop && xrefInput && typeof goToHeroSlide === 'function') {
					e.preventDefault();
					goToHeroSlide(1);
					scrollToElement(heroTop);
					setTimeout(function () {
						xrefInput.focus({ preventScroll: true });
					}, 350);
				}
			});
		});

		// Delegated clicks: active filter chips, Reset / Clear all, and numbered pagination.
		document.addEventListener('click', function (e) {
			var chipBtn = e.target.closest('.js-remove-chip');
			if (chipBtn) {
				e.preventDefault();
				var type = chipBtn.getAttribute('data-chip-type');
				var val = chipBtn.getAttribute('data-chip-val');
				var cur = getCatalogStateFromDOM(1);

				if (type === 'q') {
					setCatalogStateInDOM({ q: '' });
				} else if (type === 'cat') {
					setCatalogStateInDOM({ cats: cur.cats.filter(function (c) { return c !== val; }) });
				} else if (type === 'brand') {
					setCatalogStateInDOM({ brands: cur.brands.filter(function (b) { return b !== val; }) });
				} else if (type === 'volt') {
					setCatalogStateInDOM({ volts: cur.volts.filter(function (v) { return v !== val; }) });
				} else if (type === 'price') {
					setCatalogStateInDOM({ price: 'any' });
				}
				fetchCatalog({ page: 1 });
				return;
			}

			var resetBtn = e.target.closest('.js-cedra-reset-filters');
			if (resetBtn) {
				e.preventDefault();
				setCatalogStateInDOM({
					cats: [],
					brands: [],
					volts: [],
					price: 'any',
					q: ''
				});
				fetchCatalog({ page: 1 });
				return;
			}

			var pageBtn = e.target.closest('.js-catalog-page');
			if (pageBtn) {
				e.preventDefault();
				var targetPage = parseInt(pageBtn.getAttribute('data-page') || '1', 10);
				fetchCatalog({ page: Math.max(1, targetPage), scrollToResults: true });
			}
		});

		window.addEventListener('popstate', function (e) {
			if (e.state && typeof e.state === 'object') {
				setCatalogStateInDOM(e.state);
				fetchCatalog({ page: e.state.page || 1, updateUrl: false });
			}
		});
	}

	/* ======================================================================
	   5. Slide-Over Mini-Cart Drawer & AJAX Add-to-Cart
	   ====================================================================== */

	var cartDrawer = document.getElementById('cedra-cart-drawer');
	var cartDrawerPanel = document.querySelector('.js-cedra-drawer-panel');

	function setCartDrawerOpen(open) {
		if (!cartDrawer) return;
		cartDrawer.classList.toggle('is-open', open);
		cartDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
		document.body.style.overflow = open ? 'hidden' : '';
	}

	function updateCartBadgeAndDrawer(count, drawerHtml) {
		if (typeof count === 'number') {
			document.querySelectorAll('.js-cedra-cart-count').forEach(function (el) {
				el.textContent = String(count);
			});
			document.querySelectorAll('.cedra-cart-btn').forEach(function (btn) {
				btn.setAttribute('aria-label', 'Cart, ' + count + ' items');
			});
		}
		if (typeof drawerHtml === 'string' && cartDrawerPanel) {
			cartDrawerPanel.innerHTML = drawerHtml;
		}
	}

	function sendCartAction(params, onDone) {
		var body = new URLSearchParams();
		body.set('action', 'cedra_cart_action');
		body.set('nonce', cfg.nonce);
		Object.keys(params).forEach(function (k) {
			body.set(k, String(params[k]));
		});

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res && res.success && res.data) {
					updateCartBadgeAndDrawer(res.data.count, res.data.drawerHtml);
				} else if (res && res.data && res.data.redirect) {
					window.location.href = res.data.redirect;
					return;
				}
				if (typeof onDone === 'function') onDone(res);
			})
			.catch(function () {
				if (typeof onDone === 'function') onDone(null);
			});
	}

	document.querySelectorAll('.js-open-cart-drawer').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			if (!cartDrawer) return;
			e.preventDefault();
			setCartDrawerOpen(true);
		});
	});

	document.addEventListener('click', function (e) {
		if (e.target.closest('.js-close-cart-drawer')) {
			setCartDrawerOpen(false);
			return;
		}

		var addBtn = e.target.closest('.js-cedra-add-to-cart');
		if (addBtn) {
			e.preventDefault();
			var pid = addBtn.getAttribute('data-product-id');
			if (!pid || addBtn.classList.contains('is-loading')) return;

			var originalText = addBtn.textContent;
			addBtn.classList.add('is-loading');
			addBtn.textContent = 'Adding…';

			sendCartAction({ op: 'add', product_id: pid, quantity: 1 }, function (res) {
				addBtn.classList.remove('is-loading');
				if (res && res.success) {
					addBtn.classList.add('is-added');
					addBtn.textContent = 'Added ✓';
					setCartDrawerOpen(true);
					setTimeout(function () {
						addBtn.classList.remove('is-added');
						addBtn.textContent = originalText;
					}, 1400);
				} else {
					addBtn.textContent = originalText;
				}
			});
			return;
		}

		var qtyBtn = e.target.closest('.js-drawer-qty');
		if (qtyBtn) {
			e.preventDefault();
			var cartKey = qtyBtn.getAttribute('data-cart-key');
			var nextQty = parseInt(qtyBtn.getAttribute('data-qty') || '0', 10);
			if (!cartKey) return;
			sendCartAction({ op: 'update', cart_key: cartKey, quantity: Math.max(0, nextQty) });
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			setCartDrawerOpen(false);
			setFiltersDrawerOpen(false);
		}
	});

	/* ======================================================================
	   6. Single Product Page (Gallery Switcher, Quantity Stepper & AJAX Cart)
	   ====================================================================== */

	var mainPdpImg = document.querySelector('.js-pdp-main-img');
	document.querySelectorAll('.js-pdp-thumb').forEach(function (thumb) {
		thumb.addEventListener('click', function () {
			if (!mainPdpImg) return;
			var fullSrc = thumb.getAttribute('data-full-src');
			var srcset = thumb.getAttribute('data-srcset');
			if (fullSrc) {
				mainPdpImg.setAttribute('src', fullSrc);
				if (srcset) {
					mainPdpImg.setAttribute('srcset', srcset);
				} else {
					mainPdpImg.removeAttribute('srcset');
				}
			}
			document.querySelectorAll('.js-pdp-thumb').forEach(function (t) {
				t.classList.toggle('is-active', t === thumb);
			});
		});
	});

	var pdpQtyInput = document.querySelector('.js-pdp-qty-input');
	document.querySelectorAll('.js-pdp-qty-step').forEach(function (stepBtn) {
		stepBtn.addEventListener('click', function () {
			if (!pdpQtyInput) return;
			var delta = parseInt(stepBtn.getAttribute('data-step') || '1', 10);
			var cur = parseInt(pdpQtyInput.value || '1', 10);
			var min = parseInt(pdpQtyInput.getAttribute('min') || '1', 10);
			var maxAttr = pdpQtyInput.getAttribute('max');
			var next = Math.max(min, (isNaN(cur) ? 1 : cur) + delta);
			if (maxAttr) {
				var max = parseInt(maxAttr, 10);
				if (!isNaN(max) && max > 0) next = Math.min(max, next);
			}
			pdpQtyInput.value = String(next);
		});
	});

	var pdpCartForm = document.querySelector('.js-pdp-cart-form');
	if (pdpCartForm) {
		pdpCartForm.addEventListener('submit', function (e) {
			var submitBtn = pdpCartForm.querySelector('.js-pdp-ajax-add');
			if (!submitBtn) return;
			var pid = submitBtn.getAttribute('data-product-id') || submitBtn.value;
			if (!pid) return;

			e.preventDefault();
			var qty = pdpQtyInput ? Math.max(1, parseInt(pdpQtyInput.value || '1', 10)) : 1;
			var originalLabel = submitBtn.textContent;
			submitBtn.disabled = true;
			submitBtn.textContent = 'Adding…';

			sendCartAction({ op: 'add', product_id: pid, quantity: qty }, function (res) {
				submitBtn.disabled = false;
				submitBtn.textContent = originalLabel;
				if (res && res.success) {
					setCartDrawerOpen(true);
				} else {
					pdpCartForm.submit();
				}
			});
		});
	}
})();
