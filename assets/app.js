/* Welcome to your app's main JavaScript file! */

import './vendor/bootstrap-icons/font/bootstrap-icons.css';
import './vendor/hs-mega-menu/dist/hs-mega-menu.min.css';
import './vendor/hs-video-bg/dist/hs-video-bg.css';
import './vendor/swiper/swiper-bundle.css';
import './styles/theme.min.css';
import 'select2/dist/css/select2.css';
import 'cropperjs/dist/cropper.css';
// Custom overrides should load after vendor/theme styles
import './styles/app.css';

// Importez les dépendances
import $ from 'jquery';
import Popper from 'popper.js';
import 'bootstrap';
import HSHeader from './vendor/hs-header/dist/hs-header.min.js';
import HSStickyBlock from './vendor/hs-sticky-block/dist/hs-sticky-block.min.js';
import HSMegaMenu from './vendor/hs-mega-menu/dist/hs-mega-menu.min.js';
import HSGoTo from './vendor/hs-go-to/dist/hs-go-to.min.js';
import Swiper from 'swiper/bundle';
import HSVideoBg from './vendor/hs-video-bg/dist/hs-video-bg.min.js';
import FsLightbox from './vendor/fslightbox/index.js';
import Cropper from 'cropperjs';

import './js/theme.min.js';

// Assurez-vous que jQuery et Popper.js sont disponibles globalement (nécessaire pour certaines parties du thème Unify)
window.$ = window.jQuery = $;
window.Popper = Popper;
require('select2');

// start the Stimulus application
import './bootstrap';

$(document).ready(function () {
  // INITIALIZATION OF NAVBAR
  // =======================================================
  new HSHeader('#header').init();

  const getBackgroundImageUrl = (element) => {
    const style = window.getComputedStyle(element);
    const bg = style.backgroundImage || '';
    const match = /url\(["']?(.+?)["']?\)/.exec(bg);
    return match ? match[1] : '';
  };

  const analyzeProjectCardTone = (card) => {
    if (!card) return;
    const url = getBackgroundImageUrl(card);
    if (!url) return;
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) return;
        const w = 40;
        const h = 40;
        canvas.width = w;
        canvas.height = h;
        ctx.drawImage(img, 0, 0, w, h);
        const data = ctx.getImageData(0, 0, w, h).data;
        let total = 0;
        for (let i = 0; i < data.length; i += 4) {
          const r = data[i];
          const g = data[i + 1];
          const b = data[i + 2];
          total += 0.2126 * r + 0.7152 * g + 0.0722 * b;
        }
        const avg = total / (data.length / 4);
        card.classList.toggle('is-light', avg > 150);
        card.classList.toggle('is-dark', avg <= 150);
      } catch (e) {
        card.classList.add('is-dark');
      }
    };
    img.onerror = () => {
      card.classList.add('is-dark');
    };
    img.src = url;
  };

  const analyzeProjectCards = () => {
    document.querySelectorAll('.oling-project-card').forEach(analyzeProjectCardTone);
  };

  const scheduleProjectCardAnalysis = () => {
    analyzeProjectCards();
    setTimeout(analyzeProjectCards, 500);
    setTimeout(analyzeProjectCards, 1500);
  };

  const parseColor = (value) => {
    if (!value) return null;
    const v = value.trim();
    if (v.startsWith('#')) {
      let hex = v.slice(1);
      if (hex.length === 3) {
        hex = hex.split('').map((c) => c + c).join('');
      }
      if (hex.length === 6) {
        const r = parseInt(hex.slice(0, 2), 16);
        const g = parseInt(hex.slice(2, 4), 16);
        const b = parseInt(hex.slice(4, 6), 16);
        return [r, g, b];
      }
    }
    const rgb = v.match(/rgba?\(([^)]+)\)/i);
    if (rgb && rgb[1]) {
      const parts = rgb[1].split(',').map((p) => parseFloat(p.trim()));
      if (parts.length >= 3) {
        return [parts[0], parts[1], parts[2]];
      }
    }
    return null;
  };

  const luminance = ([r, g, b]) => 0.2126 * r + 0.7152 * g + 0.0722 * b;

  const applyTone = (el, tone) => {
    if (!el) return;
    el.classList.toggle('tone-light', tone === 'light');
    el.classList.toggle('tone-dark', tone !== 'light');
  };

  const computeToneFromVars = (el, vars, threshold = 150) => {
    const styles = window.getComputedStyle(el);
    const colors = vars
      .map((key) => parseColor(styles.getPropertyValue(key)))
      .filter(Boolean);
    if (!colors.length) return null;
    const avg = colors.reduce((sum, c) => sum + luminance(c), 0) / colors.length;
    return avg > threshold ? 'light' : 'dark';
  };

  const updatePracticeTone = () => {
    document.querySelectorAll('.acc-practice-step').forEach((card) => {
      const tone = computeToneFromVars(card, ['--practice-accent', '--card-grad-2']);
      if (tone) applyTone(card, tone);
    });

    const hero = document.querySelector('.oling-practice-hero');
    if (hero) {
      const tone = computeToneFromVars(hero, ['--hero-color-1', '--hero-color-2', '--practice-accent', '--card-grad-2']);
      if (tone) applyTone(hero, tone);
    }
  };

  const initGeoMap = () => {
    const mapEl = document.getElementById('oling-geo-map');
    if (!mapEl || mapEl.dataset.ready === '1') return;
    if (typeof window.L === 'undefined') return;
    mapEl.dataset.ready = '1';

    const map = window.L.map(mapEl, {
      zoomControl: false,
      scrollWheelZoom: false,
      attributionControl: false,
      preferCanvas: true,
      zoomSnap: 0.25,
      zoomDelta: 0.25,
    });

    const geojsonScript = document.getElementById('oling-geojson-data');
    const geojsonText = geojsonScript ? geojsonScript.textContent : null;
    const worldUrl = mapEl.dataset.world;
    let worldLayer = null;
    let franceLayer = null;
    const markerStyle = {
      radius: 6,
      color: '#0093dc',
      weight: 2,
      fillColor: '#0093dc',
      fillOpacity: 0.9,
    };

    const points = [
      { name: 'Paris', lat: 48.8566, lng: 2.3522 },
      { name: 'Rouen', lat: 49.4432, lng: 1.0993 },
      { name: 'Nantes', lat: 47.2184, lng: -1.5536 },
      { name: 'Lyon', lat: 45.7640, lng: 4.8357 },
      { name: 'Toulouse', lat: 43.6047, lng: 1.4442 },
      { name: 'Montpellier', lat: 43.6108, lng: 3.8767 },
      { name: 'Marseille', lat: 43.2965, lng: 5.3698 },
      { name: 'Figeac', lat: 44.6084, lng: 2.0316 },
      { name: 'Guadeloupe', lat: 16.2650, lng: -61.5510 },
      { name: 'Martinique', lat: 14.6415, lng: -61.0242 },
      { name: 'Saint‑Martin', lat: 18.0708, lng: -63.0501 },
      { name: 'Guyane', lat: 4.9224, lng: -52.3135 },
      { name: 'La Réunion', lat: -21.1151, lng: 55.5364 },
      { name: 'Saint‑Pierre‑et‑Miquelon', lat: 46.7772, lng: -56.1773 },
    ];

    const markerGroup = window.L.featureGroup();
    points.forEach((point) => {
      window.L.circleMarker([point.lat, point.lng], markerStyle)
        .addTo(markerGroup);
    });

    const loadWorld = async () => {
      if (!worldUrl) return null;
      try {
        const response = await fetch(worldUrl, { cache: 'force-cache' });
        if (!response.ok) return null;
        return await response.json();
      } catch (e) {
        return null;
      }
    };

    const applyLayers = async () => {
      const worldData = await loadWorld();
      if (worldData) {
        worldLayer = window.L.geoJSON(worldData, {
          style: () => ({
            color: 'rgba(255,255,255,0.28)',
            weight: 1.1,
            fill: false,
            fillOpacity: 0,
            fillColor: 'transparent',
            className: 'oling-world-layer',
          }),
        }).addTo(map);
      }

      if (geojsonText) {
        try {
          const data = JSON.parse(geojsonText);
          franceLayer = window.L.geoJSON(data, {
            style: () => ({
              color: 'rgba(255,255,255,0.95)',
              weight: 2.2,
              fillColor: 'rgba(0,147,220,0.55)',
              fillOpacity: 0.9,
              className: 'oling-france-layer',
            }),
          }).addTo(map);
          franceLayer.bringToFront();
        } catch (e) {
          franceLayer = null;
        }
      }

      markerGroup.addTo(map);
      const mapSize = map.getSize();
      const offsetX = Math.round((mapSize?.x ?? 0) * 0.1);
      const offsetY = Math.round((mapSize?.y ?? 0) * 0.1);
      map.fitWorld({
        paddingTopLeft: [offsetX, offsetY],
        paddingBottomRight: [10, 10],
      });
      const targetZoom = 2.35;
      map.setZoom(targetZoom);
    };

    applyLayers();
    setTimeout(() => {
      map.invalidateSize();
    }, 200);
  };

  // COOKIE CONSENT
  // =======================================================
  const consentKey = 'oling_cookie_consent';
  const consentAll = 'all';
  const consentMinimal = 'minimal';
  const defaultConsent = {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    functionality_storage: 'granted',
    security_storage: 'granted',
  };

  const setConsentCookie = (value) => {
    const maxAge = 60 * 60 * 24 * 180;
    const secure = window.location.protocol === 'https:' ? '; secure' : '';
    document.cookie = `${consentKey}=${value}; path=/; max-age=${maxAge}; samesite=lax${secure}`;
  };

  const initConsentMode = () => {
    if (window.__olingConsentModeReady) return;
    const gaId = document.body?.dataset?.gaId;
    if (!gaId) return;

    window.__olingConsentModeReady = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };

    // Consent Mode v2 defaults to denied until user action.
    window.gtag('consent', 'default', {
      ...defaultConsent,
      wait_for_update: 500,
    });
    window.gtag('js', new Date());
    window.gtag('config', gaId, {
      anonymize_ip: true,
      allow_google_signals: false,
      allow_ad_personalization_signals: false,
    });

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
    document.head.appendChild(script);
  };

  const updateConsentMode = (value) => {
    if (typeof window.gtag !== 'function') return;

    if (value === consentAll) {
      window.gtag('consent', 'update', {
        ...defaultConsent,
        analytics_storage: 'granted',
      });
      return;
    }

    window.gtag('consent', 'update', {
      ...defaultConsent,
      analytics_storage: 'denied',
    });
  };

  const applyConsent = (value, persist = true) => {
    document.body.dataset.cookieConsent = value;
    if (persist) {
      localStorage.setItem(consentKey, value);
      setConsentCookie(value);
    }
    const banner = document.getElementById('cookie-banner');
    if (banner) {
      banner.classList.remove('is-visible');
      banner.classList.remove('is-details-open');
    }
    updateConsentMode(value);
  };

  const setAnalyticsCheckbox = (value) => {
    const checkbox = document.querySelector('.js-cookie-analytics');
    if (checkbox) {
      checkbox.checked = value;
    }
  };

  const banner = document.getElementById('cookie-banner');
  const bindCookieActions = () => {
    if (!banner) return;
    const acceptBtn = banner.querySelector('.js-cookie-accept');
    const refuseBtn = banner.querySelector('.js-cookie-refuse');
    const detailsBtn = banner.querySelector('.js-cookie-details');
    const saveBtn = banner.querySelector('.js-cookie-save');
    if (acceptBtn) {
      acceptBtn.addEventListener('click', () => {
        setAnalyticsCheckbox(true);
        applyConsent(consentAll);
      });
    }
    if (refuseBtn) {
      refuseBtn.addEventListener('click', () => {
        setAnalyticsCheckbox(false);
        applyConsent(consentMinimal);
      });
    }
    if (detailsBtn) {
      detailsBtn.addEventListener('click', () => {
        banner.classList.toggle('is-details-open');
      });
    }
    if (saveBtn) {
      saveBtn.addEventListener('click', () => {
        const checkbox = banner.querySelector('.js-cookie-analytics');
        const value = checkbox && checkbox.checked ? consentAll : consentMinimal;
        applyConsent(value);
      });
    }
  };

  bindCookieActions();

  initConsentMode();

  const existingConsent = localStorage.getItem(consentKey);
  if (existingConsent === consentAll || existingConsent === consentMinimal) {
    setAnalyticsCheckbox(existingConsent === consentAll);
    applyConsent(existingConsent, false);
  } else if (banner) {
    setAnalyticsCheckbox(false);
    banner.classList.add('is-visible');
  }

  document.querySelectorAll('.js-cookie-manage').forEach((button) => {
    button.addEventListener('click', () => {
      if (!banner) return;
      const currentConsent = localStorage.getItem(consentKey);
      setAnalyticsCheckbox(currentConsent === consentAll);
      banner.classList.add('is-visible', 'is-details-open');
      banner.scrollIntoView({ behavior: 'smooth', block: 'end' });
    });
  });

  // INITIALIZATION OF MEGA MENU
  // =======================================================
  const megaMenu = new HSMegaMenu('.js-mega-menu', {
    desktop: {
      position: 'left'
    }
  });

  // Mobile menu: keep Bootstrap default behavior (no custom full-screen overlay).


  // INITIALIZATION OF VIDEO BG
    // =======================================================
    document.querySelectorAll('.js-video-bg').forEach(item=> {
      new HSVideoBg(item).init()
      console.log('video')
    })

  // INITIALIZATION OF GO TO
  // =======================================================
  new HSGoTo('.js-go-to');

  // INITIALIZATION OF SELECT2 (admin)
  // =======================================================
  const $adminSelects = $('.admin select').not('.js-no-select2');
  if ($adminSelects.length) {
    $adminSelects.each(function () {
      const $el = $(this);
      if ($el.data('select2')) {
        return;
      }
      $el.select2({
        width: '100%',
        dropdownAutoWidth: false,
      });
    });
  }

  // ADMIN TABLE SEARCH + SORT
  // =======================================================
  function normalizeText(value) {
    return (value || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function getCellValue(row, index) {
    const cell = row.children[index];
    if (!cell) {
      return '';
    }
    const sortValue = cell.getAttribute('data-sort-value');
    if (sortValue !== null) {
      return sortValue;
    }
    return cell.textContent.trim();
  }

  function detectNumeric(values) {
    return values.length > 0 && values.every((value) => value !== '' && !Number.isNaN(Number(value)));
  }

  function enhanceAdminTable(table) {
    const tbody = table.tBodies[0];
    if (!tbody) {
      return;
    }

    const rows = Array.from(tbody.rows);
    const toolbar = document.createElement('div');
    toolbar.className = 'admin-table-toolbar';

    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'form-control';
    search.placeholder = 'Rechercher dans le tableau...';
    search.autocomplete = 'off';

    const count = document.createElement('div');
    count.className = 'admin-table-count text-muted';
    count.textContent = `${rows.length} éléments`;

    toolbar.appendChild(search);
    toolbar.appendChild(count);

    table.parentNode.insertBefore(toolbar, table);

    const headers = Array.from(table.querySelectorAll('thead th'));
    headers.forEach((th, index) => {
      if (th.classList.contains('no-sort')) {
        return;
      }
      th.classList.add('admin-sortable');
      th.addEventListener('click', () => {
        const current = th.getAttribute('data-sort') || 'none';
        const next = current === 'asc' ? 'desc' : 'asc';

        headers.forEach((header) => header.removeAttribute('data-sort'));
        th.setAttribute('data-sort', next);

        const values = rows.map((row) => getCellValue(row, index));
        const isNumeric = detectNumeric(values);

        rows.sort((a, b) => {
          const aValue = getCellValue(a, index);
          const bValue = getCellValue(b, index);

          if (isNumeric) {
            const aNum = Number(aValue);
            const bNum = Number(bValue);
            return next === 'asc' ? aNum - bNum : bNum - aNum;
          }

          const aText = normalizeText(aValue);
          const bText = normalizeText(bValue);
          if (aText === bText) {
            return 0;
          }
          if (next === 'asc') {
            return aText > bText ? 1 : -1;
          }
          return aText < bText ? 1 : -1;
        });

        rows.forEach((row) => tbody.appendChild(row));
      });
    });

    search.addEventListener('input', () => {
      const query = normalizeText(search.value);
      let visibleCount = 0;
      rows.forEach((row) => {
        const text = normalizeText(row.textContent);
        const isVisible = text.includes(query);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) {
          visibleCount += 1;
        }
      });
      count.textContent = `${visibleCount} élément${visibleCount > 1 ? 's' : ''}`;
    });
  }

  document.querySelectorAll('.admin table.js-admin-table').forEach(enhanceAdminTable);

  // ADMIN CROPPER (hero cards)
  // =======================================================
  const cropperRegistry = new Map();

  function buildCroppedFile(file, cropper, aspectRatio) {
    return new Promise((resolve) => {
      const mimeType = file.type && file.type.startsWith('image/') ? file.type : 'image/jpeg';
      const extension = mimeType === 'image/png' ? 'png' : 'jpg';
      const filename = file.name ? file.name.replace(/\.[^.]+$/, '') : 'hero';
      const targetWidth = 900;
      const safeRatio = Number.isNaN(aspectRatio) ? 0.8 : (aspectRatio || 0.8);
      const targetHeight = Math.round(targetWidth / safeRatio);

      const canvas = cropper.getCroppedCanvas({
        width: targetWidth,
        height: targetHeight,
        imageSmoothingQuality: 'high',
      });

      canvas.toBlob((blob) => {
        if (!blob) {
          resolve(null);
          return;
        }
        const croppedFile = new File([blob], `${filename}-hero.${extension}`, { type: mimeType });
        resolve(croppedFile);
      }, mimeType, 0.92);
    });
  }

  function setupCropperInput(input) {
    const inputId = input.id;
    const wrapper = inputId ? document.querySelector(`.admin-cropper[data-input="${inputId}"]`) : null;
    const resolvedWrapper = wrapper || input.closest('.admin-cropper');
    if (!resolvedWrapper) {
      return;
    }
    const preview = resolvedWrapper.querySelector('.admin-cropper__image');
    if (!preview) {
      return;
    }

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) {
        return;
      }

      const datasetAspect = input.dataset.cropAspect || '0.8';
      const aspectRatio = datasetAspect === 'free' ? NaN : parseFloat(datasetAspect);
      const existing = cropperRegistry.get(input);
      if (existing) {
        existing.destroy();
        cropperRegistry.delete(input);
      }

      const objectUrl = URL.createObjectURL(file);
      preview.src = objectUrl;
      preview.onload = () => {
        URL.revokeObjectURL(objectUrl);
      };
      resolvedWrapper.classList.add('is-active');

      const cropper = new Cropper(preview, {
        aspectRatio: Number.isNaN(aspectRatio) ? NaN : aspectRatio,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        background: false,
        responsive: true,
      });

      cropperRegistry.set(input, cropper);
    });
  }

  function setupCropperForms() {
    const forms = document.querySelectorAll('form');
    forms.forEach((form) => {
      if (form.dataset.cropperBound === '1') {
        return;
      }
      form.dataset.cropperBound = '1';

      form.addEventListener('submit', async (event) => {
        const inputs = Array.from(form.querySelectorAll('.js-cropper-input'));
        const activeInputs = inputs.filter((input) => cropperRegistry.has(input));
        if (activeInputs.length === 0) {
          return;
        }
        if (form.dataset.cropperProcessing === '1') {
          return;
        }

        event.preventDefault();
        form.dataset.cropperProcessing = '1';

        for (const input of activeInputs) {
          const cropper = cropperRegistry.get(input);
          const file = input.files && input.files[0];
          if (!cropper || !file) {
            continue;
          }

          const datasetAspect = input.dataset.cropAspect || '0.8';
          const aspectRatio = datasetAspect === 'free' ? NaN : parseFloat(datasetAspect);
          const croppedFile = await buildCroppedFile(file, cropper, aspectRatio);
          if (croppedFile) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            input.files = dataTransfer.files;
          }
        }

        form.submit();
      });
    });
  }

  document.querySelectorAll('.js-cropper-input').forEach(setupCropperInput);
  setupCropperForms();

  function applyCropperRatio(select) {
    const targetId = select.dataset.cropInput;
    const input = targetId ? document.getElementById(targetId) : null;
    if (!input) {
      return;
    }
    const value = select.value;
    const aspect = value === 'free' ? NaN : parseFloat(value);
    if (!Number.isNaN(aspect)) {
      input.dataset.cropAspect = String(aspect);
    } else {
      input.dataset.cropAspect = 'free';
    }
    const cropper = cropperRegistry.get(input);
    if (cropper) {
      cropper.setAspectRatio(aspect);
      cropper.crop();
    }
  }

  document.querySelectorAll('.js-cropper-ratio').forEach((select) => {
    select.addEventListener('change', () => applyCropperRatio(select));
    select.addEventListener('input', () => applyCropperRatio(select));
    select.addEventListener('select2:select', () => applyCropperRatio(select));
  });

  document.querySelectorAll('.js-cropper-use-existing').forEach((button) => {
    button.addEventListener('click', async () => {
      const wrapper = button.closest('.admin-cropper') || button.closest('.admin-media-card')?.querySelector('.admin-cropper');
      if (!wrapper) {
        return;
      }
      const inputId = wrapper.dataset.input;
      const input = inputId ? document.getElementById(inputId) : wrapper.previousElementSibling?.querySelector('.js-cropper-input');
      const preview = wrapper.querySelector('.admin-cropper__image');
      if (!input || !preview || !preview.src) {
        return;
      }

      button.disabled = true;
      button.textContent = 'Chargement...';

      try {
        const response = await fetch(preview.src, { cache: 'no-store' });
        if (!response.ok) {
          throw new Error('Image introuvable');
        }
        const blob = await response.blob();
        const mimeType = blob.type && blob.type.startsWith('image/') ? blob.type : 'image/jpeg';
        const baseName = (preview.src.split('/').pop() || 'hero').split('?')[0].replace(/\.[^.]+$/, '');
        const extension = mimeType === 'image/png' ? 'png' : 'jpg';
        const file = new File([blob], `${baseName}-hero.${extension}`, { type: mimeType });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      } catch (error) {
        alert('Impossible de charger l’image existante pour le recadrage.');
        console.error(error);
      } finally {
        button.disabled = false;
        button.textContent = 'Recadrer l’existante';
      }
    });
  });

  // ADMIN ICON PICKER (Bootstrap Icons)
  // =======================================================
  let cachedBootstrapIcons = null;

  function extractBootstrapIcons() {
    if (cachedBootstrapIcons) {
      return cachedBootstrapIcons;
    }
    const icons = new Set();
    const regex = /\.bi-([a-z0-9-]+)::before/;

    for (const sheet of Array.from(document.styleSheets)) {
      let rules;
      try {
        rules = sheet.cssRules;
      } catch (error) {
        continue;
      }
      if (!rules) {
        continue;
      }
      for (const rule of Array.from(rules)) {
        if (!rule.selectorText) {
          continue;
        }
        const selectors = rule.selectorText.split(',');
        selectors.forEach((selector) => {
          const match = selector.trim().match(regex);
          if (match && match[1]) {
            icons.add(`bi-${match[1]}`);
          }
        });
      }
    }

    cachedBootstrapIcons = Array.from(icons).sort();
    return cachedBootstrapIcons;
  }

  function isImageValue(value) {
    return value.startsWith('/') || value.startsWith('http');
  }

  function updateIconPreview(input, preview, label) {
    const value = input.value.trim();
    label.textContent = value;
    preview.innerHTML = '';

    if (!value) {
      label.textContent = '';
      preview.innerHTML = '<span class="text-muted">Aucune</span>';
      return;
    }

    if (isImageValue(value)) {
      const img = document.createElement('img');
      img.src = value;
      img.alt = 'Icône';
      img.className = 'admin-icon-picker__img';
      preview.appendChild(img);
      return;
    }

    const icon = document.createElement('i');
    icon.className = value;
    preview.appendChild(icon);
  }

  function initIconPicker(wrapper) {
    const input = wrapper.querySelector('.js-icon-picker-input');
    const toggle = wrapper.querySelector('.js-icon-picker-toggle');
    const panel = wrapper.querySelector('.js-icon-picker-panel');
    const search = wrapper.querySelector('.js-icon-picker-search');
    const grid = wrapper.querySelector('.js-icon-picker-grid');
    const hint = wrapper.querySelector('.js-icon-picker-hint');
    const preview = wrapper.querySelector('.js-icon-picker-preview');
    const label = wrapper.querySelector('.js-icon-picker-label');

    if (!input || !panel || !grid || !search || !preview || !label) {
      return;
    }

    const icons = extractBootstrapIcons();
    const limit = 240;
    let rendered = false;

    function renderIcons(filter) {
      const query = normalizeText(filter);
      let visibleIcons = icons;
      if (query.length >= 2) {
        visibleIcons = icons.filter((name) => normalizeText(name).includes(query));
        hint.textContent = `${visibleIcons.length} icône${visibleIcons.length > 1 ? 's' : ''}`;
      } else {
        visibleIcons = icons.slice(0, limit);
        hint.textContent = icons.length > limit
          ? `Affichage limité à ${limit} icônes. Tapez 2 caractères pour filtrer.`
          : `${icons.length} icône${icons.length > 1 ? 's' : ''}`;
      }

      grid.innerHTML = '';
      const fragment = document.createDocumentFragment();
      visibleIcons.forEach((name) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'admin-icon-picker__item';
        button.setAttribute('data-icon', name);
        button.setAttribute('title', name);
        button.innerHTML = `<i class="${name}"></i>`;
        fragment.appendChild(button);
      });
      grid.appendChild(fragment);
    }

    function openPanel() {
      panel.classList.add('is-open');
      if (!rendered) {
        renderIcons('');
        rendered = true;
      }
      search.focus();
    }

    function closePanel() {
      panel.classList.remove('is-open');
    }

    toggle.addEventListener('click', () => {
      if (panel.classList.contains('is-open')) {
        closePanel();
      } else {
        openPanel();
      }
    });

    document.addEventListener('click', (event) => {
      if (!wrapper.contains(event.target)) {
        closePanel();
      }
    });

    search.addEventListener('input', () => {
      renderIcons(search.value);
    });

    grid.addEventListener('click', (event) => {
      const target = event.target.closest('[data-icon]');
      if (!target) {
        return;
      }
      const icon = target.getAttribute('data-icon');
      input.value = icon;
      updateIconPreview(input, preview, label);
      closePanel();
    });

    input.addEventListener('input', () => {
      updateIconPreview(input, preview, label);
    });

    updateIconPreview(input, preview, label);
  }

  document.querySelectorAll('.admin .js-icon-picker').forEach(initIconPicker);

  (function() {
     // INITIALIZATION OF SWIPER
      // =======================================================
      var sliderThumbs = new Swiper('.js-swiper-blog-modern-hero-thumbs', {
        slidesPerView: 4,
        breakpoints: {
          580: {
            slidesPerView: 3,
            spaceBetween: 30,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 50,
          },
        },
      });

      // Blog Modern Hero
      var swiper = new Swiper('.js-swiper-blog-modern-hero',{
        effect: 'fade',
        autoplay: {
          delay: 11000,
        },
        loop: true,
        pagination: {
          el: '.js-swiper-blog-modern-hero-pagination',
          clickable: true,
        },
        thumbs: {
          swiper: sliderThumbs
        }
      });

      // Projets
      var swiper = new Swiper('.js-swiper-card-grid',{
        navigation: {
          nextEl: '.js-swiper-card-grid-button-next',
          prevEl: '.js-swiper-card-grid-button-prev',
        },
        slidesPerView: 1,
        spaceBetween: 30,
        loop: 2,
        breakpoints: {
          480: {
            slidesPerView: 2
          },
          768: {
            slidesPerView: 2
          },
          1024: {
            slidesPerView: 3
          },
        }
      });
      swiper.on('init slideChange transitionEnd', scheduleProjectCardAnalysis);
      scheduleProjectCardAnalysis();

      // Accenture-like home carousels
      if (document.querySelector('.js-swiper-acc-insights')) {
        new Swiper('.js-swiper-acc-insights', {
          slidesPerView: 1.15,
          spaceBetween: 24,
          navigation: {
            nextEl: '.js-acc-insights-next',
            prevEl: '.js-acc-insights-prev',
          },
          watchOverflow: true,
          speed: 650,
          breakpoints: {
            768: {
              slidesPerView: 2.1,
              spaceBetween: 28,
            },
            1200: {
              slidesPerView: 3.1,
              spaceBetween: 32,
            },
          },
        });
      }

      if (document.querySelector('.js-swiper-acc-value')) {
        const valueCurrent = document.querySelector('.js-acc-value-current');
        const valueTotal = document.querySelector('.js-acc-value-total');
        const valueToggle = document.querySelector('.js-acc-value-toggle');

        const updateAccValueCount = (swiper) => {
          if (valueTotal) {
            valueTotal.textContent = swiper.slides.length.toString();
          }
          if (valueCurrent) {
            valueCurrent.textContent = (swiper.realIndex + 1).toString();
          }
        };

        const valueSwiper = new Swiper('.js-swiper-acc-value', {
          slidesPerView: 1.05,
          spaceBetween: 20,
          navigation: {
            nextEl: '.js-acc-value-next',
            prevEl: '.js-acc-value-prev',
          },
          autoplay: {
            delay: 6000,
            disableOnInteraction: false,
          },
          rewind: true,
          watchOverflow: true,
          on: {
            init: updateAccValueCount,
            slideChange: updateAccValueCount,
          },
          breakpoints: {
            768: {
              slidesPerView: 2,
              spaceBetween: 24,
            },
            1200: {
              slidesPerView: 3,
              spaceBetween: 28,
            },
          },
        });

        updateAccValueCount(valueSwiper);

        if (valueToggle && valueSwiper.autoplay) {
          valueToggle.addEventListener('click', () => {
            const isPaused = valueToggle.getAttribute('data-state') === 'paused';
            if (isPaused) {
              valueSwiper.autoplay.start();
              valueToggle.setAttribute('data-state', 'playing');
              valueToggle.setAttribute('aria-pressed', 'false');
              valueToggle.textContent = 'Pause';
            } else {
              valueSwiper.autoplay.stop();
              valueToggle.setAttribute('data-state', 'paused');
              valueToggle.setAttribute('aria-pressed', 'true');
              valueToggle.textContent = 'Lecture';
            }
          });
        }
      }

      // Auto contrast for insight cards (dark vs light backgrounds)
      const insightCards = document.querySelectorAll('.acc-insight-card[data-bg]');
      insightCards.forEach((card) => {
        const src = card.getAttribute('data-bg');
        if (!src) {
          card.classList.add('is-dark');
          return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = src;

        img.onload = () => {
          try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const size = 12;
            canvas.width = size;
            canvas.height = size;
            ctx.drawImage(img, 0, 0, size, size);
            const { data } = ctx.getImageData(0, 0, size, size);
            let total = 0;
            for (let i = 0; i < data.length; i += 4) {
              const r = data[i];
              const g = data[i + 1];
              const b = data[i + 2];
              total += 0.2126 * r + 0.7152 * g + 0.0722 * b;
            }
            const avg = total / (data.length / 4);
            if (avg < 140) {
              card.classList.add('is-dark');
            } else {
              card.classList.add('is-light');
            }
          } catch (e) {
            card.classList.add('is-dark');
          }
        };

        img.onerror = () => {
          card.classList.add('is-dark');
        };
      });

  })()



  // INITIALIZATION OF STICKY BLOCKS
  // =======================================================
  new HSStickyBlock('.js-sticky-block', {
    targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
  })
  
  // Ajouter un événement de soumission du formulaire
  $('#envoiemail').on('click', function () {
    console.log($('#envoiemail'))
      // Récupérer la valeur de l'e-mail
      var email = $('#champsemail').val();

      // Envoyer une requête AJAX
      $.ajax({
          url: '/add-email',
          type: 'POST',
          data: {
              email: email
          },
          success: function (response) {
              // Vider le champ email
             $('#champsemail').val('');
              // Afficher un message de succès
              alert(response.message);
          },
          error: function (response) {
              // Afficher un message d'erreur
              alert('Une erreur est survenue lors de l\'enregistrement de l\'adresse e-mail.');
          }
      });
  });


       // email
  // =======================================================

  $('#button-send').on('click', function () {
    const $form = $('#contact-form');
    const $loading = $('#contact-loading');
    const $success = $('#contact-success');
    const $error = $('#contact-error');
    const $button = $('#button-send');

    $error.addClass('d-none').text('');
    $loading.removeClass('d-none').addClass('d-flex');
    $button.prop('disabled', true);

    $.ajax({
        url: '/send-email',
        type: 'POST',
        data: $form.serialize(),
        success: function (response) {
            if (response.success) {
                $form.addClass('d-none');
                $success.removeClass('d-none');
                $form[0].reset();
            } else {
                $error.removeClass('d-none').text(response.message || 'Une erreur est survenue lors de l’envoi.');
            }
        },
        error: function () {
            $error.removeClass('d-none').text('Une erreur est survenue lors de l’envoi du message.');
        },
        complete: function () {
            $loading.addClass('d-none').removeClass('d-flex');
            $button.prop('disabled', false);
        }
    });
});

  const adjustHeroTitle = () => {
    const title = document.querySelector('.acc-hero-title');
    if (!title) return;

    title.style.removeProperty('--hero-h1-size');
    const computed = window.getComputedStyle(title);
    const lineHeight = parseFloat(computed.lineHeight);
    if (!lineHeight) return;

    const maxHeight = lineHeight * 3 + 1;
    let fontSize = parseFloat(computed.fontSize);
    let guard = 0;

    while (title.scrollHeight > maxHeight && fontSize > 22 && guard < 40) {
      fontSize -= 1;
      title.style.setProperty('--hero-h1-size', `${fontSize}px`);
      guard += 1;
    }
  };

  let heroResizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(heroResizeTimer);
    heroResizeTimer = setTimeout(adjustHeroTitle, 150);
  });

  window.addEventListener('load', adjustHeroTitle);
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(adjustHeroTitle).catch(() => {});
  } else {
    setTimeout(adjustHeroTitle, 200);
  }

  const FsLightboxCtor = window.FsLightbox;
  if (FsLightboxCtor) {
    const lightbox = new FsLightboxCtor();

    // Initialiser le plugin
    document.querySelectorAll('.js-fslightbox').forEach((element) => {
      element.addEventListener('click', (event) => {
        event.preventDefault();
        lightbox.open(element.dataset.src.split(','));
      });
    });
  }

  scheduleProjectCardAnalysis();
  window.addEventListener('load', scheduleProjectCardAnalysis);

  updatePracticeTone();
  window.addEventListener('load', updatePracticeTone);
  initGeoMap();
  window.addEventListener('load', initGeoMap);
  window.addEventListener('resize', initGeoMap);
  window.addEventListener('resize', () => {
    clearTimeout(heroResizeTimer);
    heroResizeTimer = setTimeout(updatePracticeTone, 150);
  });
});

$(document).ready(function() {
  let lastScrollTop = 0;
  const header = document.getElementById('header');
  if (!header) return;

  const updateHeader = () => {
    const current = window.pageYOffset || document.documentElement.scrollTop;
    const isTop = current <= 40;
    const scrollingDown = current > lastScrollTop + 6;
    const scrollingUp = current < lastScrollTop - 6;

    if (isTop) {
      header.classList.remove('header-hidden', 'header-blur');
      header.classList.add('navbar-transparent');
    } else {
      header.classList.remove('navbar-transparent');
      if (scrollingDown) {
        header.classList.add('header-hidden');
        header.classList.remove('header-blur');
      } else if (scrollingUp) {
        header.classList.remove('header-hidden');
        header.classList.add('header-blur');
      }

      if (header.classList.contains('header-hidden')) {
        header.classList.remove('header-blur');
      } else {
        header.classList.add('header-blur');
      }
    }

    $('.mymenumoov').addClass('navbar-dark').removeClass('navbar-light');
    $('.mymenulink').addClass('link-light');
    $('.logo-dark-mode').show();
    $('.logo-light-mode').hide();

    lastScrollTop = current <= 0 ? 0 : current;
  };

  updateHeader();
  window.addEventListener('scroll', updateHeader);
});



$(document).ready(function() {
  // Vérifier la largeur de lécran à chaque changement de taille
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.js-swiper-blog-modern-hero .swiper-slide').removeClass('fullheight');
    } else {
      $('.js-swiper-blog-modern-hero .swiper-slide').addClass('fullheight');
    }
  }).trigger('resize');
});

$(document).ready(function() {
  $('.highlight-text').each(function() {
    var textElement = $(this);
    var text = textElement.text();
    var lines = text.split('\n');
    var highlightedText = '';

    for (var i = 0; i < lines.length; i++) {
      var line = lines[i].trim();

      // Ajoutez un espace au début et à la fin de chaque ligne
      line = ' ' + line + ' ';

      // Ajoutez la classe "highlighted-line" pour le surlignage
      line = '<span class="highlight">' + line + '</span>';

      highlightedText += line;
    }

    textElement.html(highlightedText);
  });
});



$(document).ready(function() {
  const orderLists = document.querySelectorAll('.js-home-order');
  if (!orderLists.length) return;

  orderLists.forEach((orderList) => {
    let dragging = null;

    const handleDragStart = (event) => {
      dragging = event.currentTarget;
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.dropEffect = 'move';
      event.dataTransfer.setData('text/plain', dragging.dataset.id || 'drag');
      dragging.classList.add('is-dragging');
    };

    const handleDragEnd = () => {
      if (dragging) {
        dragging.classList.remove('is-dragging');
      }
      dragging = null;
    };

    const handleDragOver = (event) => {
      event.preventDefault();
      const target = event.target.closest('.home-order-item');
      if (!target || target === dragging) return;
      const rect = target.getBoundingClientRect();
      const next = (event.clientY - rect.top) > rect.height / 2;
      orderList.insertBefore(dragging, next ? target.nextSibling : target);
      if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
      }
    };

    const saveOrder = () => {
      const ids = Array.from(orderList.querySelectorAll('.home-order-item')).map((item) => item.dataset.id);
      const endpoint = orderList.dataset.endpoint || '/admin/practices/home-order';
      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          ids,
          _token: orderList.dataset.token,
        }),
      }).catch(() => {});
    };

    orderList.querySelectorAll('.home-order-item').forEach((item) => {
      item.setAttribute('draggable', 'true');
      item.addEventListener('dragstart', handleDragStart);
      item.addEventListener('dragend', handleDragEnd);
    });

    orderList.addEventListener('dragenter', (event) => {
      event.preventDefault();
    });
    orderList.addEventListener('dragover', handleDragOver);
    orderList.addEventListener('drop', () => {
      saveOrder();
    });
  });
});

$(document).ready(function() {
  const buildToolbar = () => {
    const toolbar = document.createElement('div');
    toolbar.className = 'oling-wysiwyg-toolbar';
    const buttons = [
      { cmd: 'bold', label: 'Gras' },
      { cmd: 'italic', label: 'Italique' },
      { cmd: 'underline', label: 'Souligné' },
      { cmd: 'insertUnorderedList', label: 'Liste' },
      { cmd: 'insertOrderedList', label: 'Liste #'},
      { cmd: 'formatBlock', value: 'H3', label: 'H3' },
      { cmd: 'formatBlock', value: 'P', label: 'P' },
      { cmd: 'createLink', label: 'Lien' },
      { cmd: 'fontSize', value: '2', label: 'A-' },
      { cmd: 'fontSize', value: '3', label: 'A' },
      { cmd: 'fontSize', value: '5', label: 'A+' },
      { cmd: 'insertImage', label: 'Image' },
      { cmd: 'alignLeft', label: 'Img gauche' },
      { cmd: 'alignCenter', label: 'Img centre' },
      { cmd: 'alignRight', label: 'Img droite' },
      { cmd: 'alignFull', label: 'Img pleine' },
      { cmd: 'removeFormat', label: 'Nettoyer' },
    ];

    buttons.forEach((btn) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.dataset.cmd = btn.cmd;
      if (btn.value) {
        button.dataset.value = btn.value;
      }
      button.textContent = btn.label;
      toolbar.appendChild(button);
    });

    return toolbar;
  };

  const initWysiwyg = () => {
    document.querySelectorAll('.admin textarea.js-wysiwyg').forEach((textarea) => {
      if (textarea.dataset.wysiwygReady === '1') return;
      if (textarea.classList.contains('js-no-wysiwyg')) return;
      textarea.dataset.wysiwygReady = '1';

      const wrapper = document.createElement('div');
      wrapper.className = 'oling-wysiwyg';

      const toolbar = buildToolbar();
      const tabs = document.createElement('div');
      tabs.className = 'oling-wysiwyg-tabs';

      const tabRender = document.createElement('button');
      tabRender.type = 'button';
      tabRender.className = 'oling-wysiwyg-tab is-active';
      tabRender.textContent = 'Rendu';

      const tabHtml = document.createElement('button');
      tabHtml.type = 'button';
      tabHtml.className = 'oling-wysiwyg-tab';
      tabHtml.textContent = 'HTML';

      tabs.appendChild(tabRender);
      tabs.appendChild(tabHtml);

      const editor = document.createElement('div');
      editor.className = 'oling-wysiwyg-editor';
      editor.contentEditable = 'true';
      editor.innerHTML = textarea.value || '';
      let activeMedia = null;

      const code = document.createElement('textarea');
      code.className = 'oling-wysiwyg-code js-no-wysiwyg';
      code.value = textarea.value || '';
      code.style.display = 'none';

      textarea.style.display = 'none';
      textarea.insertAdjacentElement('beforebegin', wrapper);
      wrapper.appendChild(toolbar);
      wrapper.appendChild(tabs);
      wrapper.appendChild(editor);
      wrapper.appendChild(code);

      const syncFromEditor = () => {
        textarea.value = editor.innerHTML;
        code.value = textarea.value;
      };

      const syncFromCode = () => {
        textarea.value = code.value;
        editor.innerHTML = textarea.value;
      };

      editor.addEventListener('input', syncFromEditor);
      editor.addEventListener('blur', syncFromEditor);
      code.addEventListener('input', syncFromCode);
      code.addEventListener('blur', syncFromCode);

      const setTab = (mode) => {
        if (mode === 'html') {
          syncFromEditor();
          editor.style.display = 'none';
          code.style.display = 'block';
          tabHtml.classList.add('is-active');
          tabRender.classList.remove('is-active');
        } else {
          syncFromCode();
          editor.style.display = 'block';
          code.style.display = 'none';
          tabRender.classList.add('is-active');
          tabHtml.classList.remove('is-active');
        }
      };

      tabRender.addEventListener('click', () => setTab('render'));
      tabHtml.addEventListener('click', () => setTab('html'));

      const getMediaFromSelection = () => {
        const selection = document.getSelection();
        if (!selection || !selection.anchorNode) return null;
        const node = selection.anchorNode.nodeType === 1 ? selection.anchorNode : selection.anchorNode.parentElement;
        if (!node) return null;
        return node.closest('.wysiwyg-media');
      };

      const setMediaAlignment = (alignment) => {
        const target = activeMedia || getMediaFromSelection();
        if (!target) return;
        target.classList.remove('wysiwyg-media--left', 'wysiwyg-media--center', 'wysiwyg-media--right', 'wysiwyg-media--full');
        target.classList.add(`wysiwyg-media--${alignment}`);
        syncFromEditor();
      };

      const insertImageHtml = (src) => {
        const html = `<figure class="wysiwyg-media wysiwyg-media--left"><img src="${src}" alt=""></figure><p></p>`;
        document.execCommand('insertHTML', false, html);
        syncFromEditor();
      };

      const handleImagePick = () => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.addEventListener('change', () => {
          const file = input.files && input.files[0];
          if (!file) return;
          const reader = new FileReader();
          reader.onload = (event) => {
            const src = event.target?.result;
            if (typeof src === 'string') {
              insertImageHtml(src);
            }
          };
          reader.readAsDataURL(file);
        });
        input.click();
      };

      editor.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
          return;
        }
        const media = target.closest('.wysiwyg-media');
        if (media) {
          activeMedia = media;
          editor.querySelectorAll('.wysiwyg-media.is-selected').forEach((el) => el.classList.remove('is-selected'));
          media.classList.add('is-selected');
        } else {
          editor.querySelectorAll('.wysiwyg-media.is-selected').forEach((el) => el.classList.remove('is-selected'));
          activeMedia = null;
        }
      });

      toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button) return;
        const cmd = button.dataset.cmd;
        if (!cmd) return;

        if (cmd === 'createLink') {
          const label = window.prompt('Texte du lien ?');
          if (!label) {
            return;
          }
          const url = window.prompt('URL du lien ?');
          if (!url) {
            return;
          }
          const html = `<a href="${url}" target="_blank" rel="noopener">${label}</a>`;
          document.execCommand('insertHTML', false, html);
        } else if (cmd === 'insertImage') {
          handleImagePick();
          return;
        } else if (cmd === 'alignLeft') {
          setMediaAlignment('left');
          return;
        } else if (cmd === 'alignCenter') {
          setMediaAlignment('center');
          return;
        } else if (cmd === 'alignRight') {
          setMediaAlignment('right');
          return;
        } else if (cmd === 'alignFull') {
          setMediaAlignment('full');
          return;
        } else if (cmd === 'formatBlock') {
          const value = button.dataset.value || 'P';
          document.execCommand('formatBlock', false, value);
        } else if (cmd === 'fontSize') {
          const value = button.dataset.value || '3';
          document.execCommand('fontSize', false, value);
        } else {
          document.execCommand(cmd, false, null);
        }

        syncFromEditor();
      });
    });
  };

  initWysiwyg();
});

$(document).ready(function() {
  const grid = document.querySelector('.js-projets-mini-grid');
  const sentinel = document.querySelector('.js-projets-sentinel');
  if (!grid || !sentinel) return;

  const loader = document.querySelector('.js-projets-loader');
  const endpoint = grid.dataset.endpoint;
  let nextPage = parseInt(grid.dataset.nextPage || '2', 10);
  let loading = false;

  const hasMore = () => grid.dataset.hasMore === '1';
  const setHasMore = (value) => {
    grid.dataset.hasMore = value ? '1' : '0';
  };

  const toggleLoader = (show) => {
    if (!loader) return;
    loader.hidden = !show;
  };

  const loadMore = () => {
    if (loading || !hasMore()) return;
    loading = true;
    toggleLoader(true);
    fetch(`${endpoint}?page=${nextPage}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data && data.html) {
          grid.insertAdjacentHTML('beforeend', data.html);
        }
        if (data && typeof data.nextPage !== 'undefined') {
          nextPage = parseInt(data.nextPage, 10);
        } else {
          nextPage += 1;
        }
        setHasMore(Boolean(data && data.hasMore));
        if (!hasMore()) {
          observer.disconnect();
          toggleLoader(false);
        }
      })
      .catch(() => {})
      .finally(() => {
        loading = false;
        toggleLoader(false);
      });
  };

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          loadMore();
        }
      });
    },
    { rootMargin: '200px 0px' }
  );

  observer.observe(sentinel);
});

 
