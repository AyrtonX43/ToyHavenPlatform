{{--
    Philippine Address Cascading Dropdowns - PSGC API
    Usage: @include('partials.philippine-address-script', [
        'prefix' => '',  // or 'shipping_' for checkout
        'prefillRegion' => '',
        'prefillProvince' => '',
        'prefillCity' => '',
        'prefillBarangay' => '',
    ])
    To sync a saved address: set window.phAddressPrefill[prefix] = {region, province, city, barangay}
    then set region value and dispatch change.
--}}
@php
    $p = $prefix ?? '';
    $prefillRegion = $prefillRegion ?? '';
    $prefillProvince = $prefillProvince ?? '';
    $prefillCity = $prefillCity ?? '';
    $prefillBarangay = $prefillBarangay ?? '';
@endphp
<script>
(function() {
    const prefix = @json($p);
    let prefillRegion = @json($prefillRegion);
    let prefillProvince = @json($prefillProvince);
    let prefillCity = @json($prefillCity);
    let prefillBarangay = @json($prefillBarangay);
    const API_BASE = 'https://psgc.cloud/api';

    const regionSelect = document.getElementById(prefix + 'region');
    const provinceSelect = document.getElementById(prefix + 'province');
    const citySelect = document.getElementById(prefix + 'city');
    const barangaySelect = document.getElementById(prefix + 'barangay');

    if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) return;

    window.phAddressPrefill = window.phAddressPrefill || {};
    function getPrefill() {
        const p = window.phAddressPrefill[prefix];
        return p ? { region: p.region||'', province: p.province||'', city: p.city||'', barangay: p.barangay||'' } : { region: prefillRegion, province: prefillProvince, city: prefillCity, barangay: prefillBarangay };
    }

    function normalizeText(text) {
        if (!text) return text;
        const charMap = { 'ñ':'n','Ñ':'N','á':'a','Á':'A','é':'e','É':'E','í':'i','Í':'I','ó':'o','Ó':'O','ú':'u','Ú':'U','ü':'u','Ü':'U' };
        let n = text;
        for (let c in charMap) n = n.split(c).join(charMap[c]);
        return n;
    }

    function loadRegions() {
        fetch(API_BASE + '/regions')
            .then(r => r.json())
            .then(data => {
                data.forEach(reg => {
                    const opt = document.createElement('option');
                    opt.value = normalizeText(reg.name);
                    opt.textContent = normalizeText(reg.name);
                    opt.dataset.code = reg.code;
                    regionSelect.appendChild(opt);
                });
                const pf = getPrefill();
                if (pf.region) {
                    const r = normalizeText(pf.region);
                    if (regionSelect.querySelector('option[value="' + r + '"]')) {
                        regionSelect.value = r;
                        regionSelect.dispatchEvent(new Event('change'));
                    }
                }
            })
            .catch(e => console.error('Error loading regions:', e));
    }

    regionSelect.addEventListener('change', function() {
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        provinceSelect.disabled = citySelect.disabled = barangaySelect.disabled = true;
        if (!this.value) return;

        const opt = this.options[this.selectedIndex];
        const code = opt.dataset.code;
        const name = this.value;

        if (name.includes('NCR') || name.includes('National Capital Region') || code === '130000000') {
            provinceSelect.innerHTML = '<option value="Metro Manila">Metro Manila</option>';
            provinceSelect.value = 'Metro Manila';
            provinceSelect.style.backgroundColor = '#e9ecef';
            provinceSelect.style.cursor = 'not-allowed';
            provinceSelect.disabled = false;
            fetch(API_BASE + '/regions/' + code + '/cities-municipalities')
                .then(r => r.json())
                .then(data => {
                    data.forEach(c => {
                        const o = document.createElement('option');
                        o.value = normalizeText(c.name);
                        o.textContent = normalizeText(c.name);
                        o.dataset.code = c.code;
                        citySelect.appendChild(o);
                    });
                    citySelect.disabled = false;
                    const pf = getPrefill();
                    if (pf.city) {
                        const v = normalizeText(pf.city);
                        if (citySelect.querySelector('option[value="' + v + '"]')) {
                            citySelect.value = v;
                            citySelect.dispatchEvent(new Event('change'));
                        }
                    }
                })
                .catch(e => console.error('Error loading NCR cities:', e));
        } else {
            provinceSelect.style.backgroundColor = '';
            provinceSelect.style.cursor = '';
            provinceSelect.disabled = false;
            fetch(API_BASE + '/regions/' + code + '/provinces')
                .then(r => r.json())
                .then(data => {
                    data.forEach(p => {
                        const o = document.createElement('option');
                        o.value = normalizeText(p.name);
                        o.textContent = normalizeText(p.name);
                        o.dataset.code = p.code;
                        provinceSelect.appendChild(o);
                    });
                    const pf = getPrefill();
                    if (pf.province) {
                        const v = normalizeText(pf.province);
                        if (provinceSelect.querySelector('option[value="' + v + '"]')) {
                            provinceSelect.value = v;
                            provinceSelect.dispatchEvent(new Event('change'));
                        }
                    }
                })
                .catch(e => console.error('Error loading provinces:', e));
        }
    });

    provinceSelect.addEventListener('change', function() {
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        citySelect.disabled = barangaySelect.disabled = true;
        if (!this.value) return;
        const code = this.options[this.selectedIndex].dataset.code;
        if (!code) return;
        fetch(API_BASE + '/provinces/' + code + '/cities-municipalities')
            .then(r => r.json())
            .then(data => {
                data.forEach(c => {
                    const o = document.createElement('option');
                    o.value = normalizeText(c.name);
                    o.textContent = normalizeText(c.name);
                    o.dataset.code = c.code;
                    citySelect.appendChild(o);
                });
                citySelect.disabled = false;
                const pf = getPrefill();
                if (pf.city) {
                    const v = normalizeText(pf.city);
                    if (citySelect.querySelector('option[value="' + v + '"]')) {
                        citySelect.value = v;
                        citySelect.dispatchEvent(new Event('change'));
                    }
                }
            })
            .catch(e => console.error('Error loading cities:', e));
    });

    citySelect.addEventListener('change', function() {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
        if (!this.value) return;
        const code = this.options[this.selectedIndex].dataset.code;
        if (!code) return;
        fetch(API_BASE + '/cities-municipalities/' + code + '/barangays')
            .then(r => r.json())
            .then(data => {
                data.forEach(b => {
                    const o = document.createElement('option');
                    o.value = normalizeText(b.name);
                    o.textContent = normalizeText(b.name);
                    barangaySelect.appendChild(o);
                });
                barangaySelect.disabled = false;
                const pf = getPrefill();
                if (pf.barangay) {
                    const v = normalizeText(pf.barangay);
                    if (barangaySelect.querySelector('option[value="' + v + '"]')) {
                        barangaySelect.value = v;
                    }
                }
                delete window.phAddressPrefill[prefix];
            })
            .catch(e => console.error('Error loading barangays:', e));
    });

    // Postal code: digits only, max 4
    const postalInput = document.getElementById(prefix + 'postal_code');
    if (postalInput) {
        postalInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 4);
        });
    }

    loadRegions();
})();
</script>
