<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // --- MOCK DATA FOR PREVIEW/FALLBACK (rest of the mockStore remains the same) ---
    const mockStore = {
        users: [
            { id: 1, name: 'Zahraa', email: 'zahraa18@gmail.com', phone: '+1234567893', role: 'User', joined: '2025-02-15' },
            { id: 2, name: 'John Doe', email: 'john10@example.com', phone: '+9876543210', role: 'User', joined: '2025-02-18' }
        ],
        types: [
            { id: 1, name: 'Car', icon: 'fa-car' },
            { id: 2, name: 'Bike', icon: 'fa-motorcycle' }
        ],
        brands: [
            { id: 1, type_id: 1, name: 'Tesla', logo: 'https://upload.wikimedia.org/api/commons/e/e8/Tesla_logo.png', models: ['Model 3', 'Model Y'] },
            { id: 2, type_id: 2, name: 'Ather', logo: 'https://upload.wikimedia.org/api/commons/2/2a/Ather_Energy_Logo.svg', models: ['450X'] }
        ],
        vehicles: [
            { id: 101, ownerId: 2, typeId: 1, brandId: 1, model: 'Model 3', plate: 'KA09XX1234', color: 'White', year: 2024, type: 'Car' }
        ],
        tickets: [
            { id: 1, userId: 1, category: 'Low Battery', desc: 'Stuck near central mall', status: 'Pending', lat: 24.27, lng: 55.29, date: '2025-02-20' }
        ],
        plates: [
            { id: 1, number: 'KA 05 AB 1234', img: '' }
        ]
    };

    // --- DATA STORE (Initialized empty) ---
    let store = {
        users: [], types: [], brands: [], vehicles: [], tickets: [], plates: []
    };

    // --- HELPER FOR API CALLS ---
    const api = {
        getHeaders: () => {
            const token = localStorage.getItem('auth_token'); // Get token from local storage
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Safe check for meta tag
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }
            return headers;
        },
        get: async (url) => (await fetch(url, { headers: api.getHeaders() })).json(),
        post: async (url, data) => (await fetch(url, { method: 'POST', headers: api.getHeaders(), body: JSON.stringify(data) })).json(),
        delete: async (url) => (await fetch(url, { method: 'DELETE', headers: api.getHeaders() })).json()
    };
    
    // --- DOM HELPER (unchanged) ---
    const dom = {
        setText: (id, val) => {
            const el = document.getElementById(id);
            if(el) el.innerText = val;
        },
        setHtml: (id, html) => {
            const el = document.getElementById(id);
            if(el) el.innerHTML = html;
        }
    };

    // --- APP LOGIC (unchanged) ---
    const app = {
        init: async () => {
            await app.data.loadAll(); // Load data from Laravel or Fallback
            app.data.loadTypes();
            // Render all sections safely
            app.render.dashboard();
            app.render.tickets();
            app.render.users();
            app.render.types();
            app.render.brands();
            app.render.vehicles();
            app.render.plates();
            
            const sidebarToggle = document.getElementById('sidebarToggle');
            if(sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    document.getElementById('sidebar')?.classList.toggle('show');
                });
            }
        },

        data: {
            loadTypes: async () => {
                try {
                    const typesResponse = await api.get('http://127.0.0.1:8000/api/vehicle_categories');
                    
                    // 1. Check for success and access the 'vehicle_categories' array
                    let typesData = [];
                    if (typesResponse && typesResponse.success && Array.isArray(typesResponse.vehicle_categories)) {
                        typesData = typesResponse.vehicle_categories;
                    } else {
                        // Throw error if the expected structure is not found
                        throw new Error('API response did not contain the expected "vehicle_categories" array.');
                    }
                    
                    // 2. Map the data, providing a fallback icon
                    if (Array.isArray(typesData)) {
                        store.types = typesData.map(t => ({
                            id: t.id,
                            name: t.name,
                            // *** IMPORTANT CHANGE: The API is missing 'icon', so we use a fallback ***
                            icon: t.icon || 'fa-car' // Fallback to a default icon (e.g., 'fa-car' or 'fa-circle')
                        }));
                        console.log('✅ Types data loaded successfully from external API.');
                        
                        // Re-render the UI immediately after successful load
                        app.render.types(); 
                        
                    } else {
                        throw new Error('Processed types data was not an array.');
                    }
                } catch (error) {
                    console.error('❌ Failed to load types from API:', error);
                    // Ensure 'store.types' is reset to an empty array on failure
                    store.types = []; 
                    app.render.types(); // Call render to clear the grid if it was trying to display old data
                }
            },

            loadAll: async () => {
                try {
                    // Attempt to fetch from Laravel Backend
                    // Note: You might need to adjust this API call to pass headers if it is also protected.
                    const data = await fetch('/api/init-data').then(res => res.json());
                    store = data;
                    console.log('Data loaded from API');
                } catch (error) {
                    // Fallback for Preview / Blob environment
                    console.warn('API Connection failed (Expected in Preview Mode). Using Mock Data.');
                    store = mockStore;
                }
            }
        },

        navigate: (sectionId, element) => {
            document.querySelectorAll('.content-section').forEach(el => el.classList.add('d-none'));
            const targetSection = document.getElementById(sectionId + '-section');
            if(targetSection) targetSection.classList.remove('d-none');
            
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            if(element) element.classList.add('active');

            const titles = {
                'dashboard': 'Dashboard', 'tickets': 'Service Tickets', 'users': 'User Management',
                'vehicles': 'Vehicle Management', 'types': 'Vehicle Categories', 'brands': 'Brand & Model Master',
                'plates': 'Number Plate Repository', 'settings': 'System Settings'
            };
            const titleEl = document.getElementById('pageTitle');
            if(titleEl) titleEl.innerText = titles[sectionId];
            
            if(sectionId === 'brands') app.render.brands();
            if(sectionId === 'dashboard') app.render.dashboard();

            if(window.innerWidth < 992) {
                document.getElementById('sidebar')?.classList.remove('show');
            }
        },

        render: {
            dashboard: () => {
                // Using helper to avoid null errors
                dom.setText('stat-users', store.users.length);
                dom.setText('stat-vehicles', store.vehicles.length);
                dom.setText('stat-tickets', store.tickets.filter(t => t.status === 'Pending').length);
                dom.setText('stat-brands', store.brands.length);
                dom.setText('ticketBadge', store.tickets.filter(t => t.status === 'Pending').length);

                const tbody = document.getElementById('dashboard-tickets-body');
                if(tbody) {
                    tbody.innerHTML = store.tickets.slice(0, 5).map(t => {
                        const u = store.users.find(user => user.id === t.userId) || {name: 'Unknown'};
                        const badge = t.status === 'Pending' ? 'bg-gradient-warning' : 'bg-gradient-success';
                        return `<tr>
                            <td><span class="text-xs fw-bold">#${t.id}</span></td>
                            <td><div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=${u.name}&background=random" class="avatar me-2"><span class="text-sm">${u.name}</span></div></td>
                            <td><span class="text-xs">${t.category}</span></td>
                            <td><span class="badge ${badge}">${t.status}</span></td>
                            <td><span class="text-secondary text-xs">${new Date(t.date).toLocaleDateString()}</span></td>
                        </tr>`;
                    }).join('');
                }
            },
            
            tickets: () => {
                const tbody = document.getElementById('tickets-table-body');
                if(!tbody) return;

                tbody.innerHTML = store.tickets.map(t => {
                    const u = store.users.find(user => user.id === t.userId) || {name: 'Unknown', phone: 'N/A'};
                    return `<tr>
                        <td><span class="text-xs fw-bold">#${t.id}</span></td>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div>
                                    <img src="https://ui-avatars.com/api/?name=${u.name}&background=random" class="avatar me-3"></div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">${u.name}</h6>
                                    <p class="text-xs text-secondary mb-0">${u.phone}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs fw-bold mb-0">${t.category}</p>
                            <p class="text-xs text-secondary mb-0 text-truncate" style="max-width:150px">${t.desc}</p>
                        </td>
                        <td class="text-xs">Lat: ${t.lat}<br>Lng: ${t.lng}</td>
                        <td class="text-center"><i class="fa-solid fa-image text-muted"></i></td>
                        <td class="align-middle text-center"><span class="badge ${t.status === 'Pending' ? 'bg-warning' : 'bg-success'}">${t.status}</span></td>
                        <td>
                            <button class="btn btn-link text-secondary mb-0" onclick="app.modals.openTicketModal(${t.id})">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </td>
                    </tr>`;
                }).join('');
            },

            users: () => {
                const tbody = document.getElementById('users-table-body');
                if(!tbody) return;

                tbody.innerHTML = store.users.map(u => `
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div><img src="https://ui-avatars.com/api/?name=${u.name}&background=random" class="avatar me-3"></div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">${u.name}</h6>
                                    <p class="text-xs text-secondary mb-0">${u.email}</p>
                                </div>
                            </div>
                        </td>
                        <td><p class="text-xs fw-bold mb-0">${u.phone}</p></td>
                        <td><span class="text-secondary text-xs fw-bold">${u.joined}</span></td>
                        <td>
                            <button class="btn btn-link text-danger mb-0" onclick="app.crud.delete('users', ${u.id})">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            },

            types: () => {
                const grid = document.getElementById('types-grid');
                if(!grid) return;

                grid.innerHTML = store.types.map(t => `
                    <div class="col-xl-3 col-md-4 col-6">
                        <div class="card card-body text-center shadow-sm border-0 h-100">
                            <i class="fa-solid ${t.icon} fs-2 text-primary mb-2"></i>
                            <h6 class="mb-1">${t.name}</h6>
                            <span class="text-xs text-muted">${store.vehicles.filter(v => v.typeId === t.id).length} Vehicles</span>
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button class="btn btn-link text-danger p-0 mb-0" onclick="app.crud.delete('types', ${t.id})" title="Remove">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            },

            brands: () => {
                const grid = document.getElementById('brands-grid');
                if (!grid) return;

                grid.innerHTML = store.brands.map(b => {
                    const typeName = store.types.find(t => t.id == b.type_id)?.name || 'Unknown';
                    const modelsHtml = b.models.map(m => 
                        `<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border-0 fw-normal px-2 py-1 m-1">${m}</span>`
                    ).join('');

                    return `
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 border card-plain shadow-none position-relative">
                            <div class="card-body text-center p-4">
                                <div class="avatar avatar-xxl bg-white shadow-sm rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center border">
                                    <img src="${b.logo}" class="p-2" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                                <h5 class="mb-1 text-dark">${b.name}</h5>
                                <span class="badge bg-light text-dark mb-3">${typeName}</span>
                                <hr class="horizontal dark my-3 opacity-2">
                                <div class="d-flex flex-wrap justify-content-center">
                                    ${modelsHtml}
                                </div>
                                <div class="position-absolute bottom-0 end-0 p-3 pt-3">
                                    <a href="javascript:;" onclick="app.crud.delete('brands', ${b.id})" class="text-danger" title="Delete">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            },

            vehicles: () => {
                const tbody = document.getElementById('vehicles-table-body');
                if(!tbody) return;

                tbody.innerHTML = store.vehicles.map(v => {
                    const owner = store.users.find(u => u.id === v.ownerId);
                    const brand = store.brands.find(b => b.id === v.brandId);
                    return `<tr>
                        <td>
                            <div class="d-flex flex-column">
                                <h6 class="mb-0 text-sm">${brand?.name} ${v.model}</h6>
                                <p class="text-xs text-secondary mb-0">ID: #${v.id}</p>
                            </div>
                        </td>
                        <td><p class="text-xs font-weight-bold mb-0">${owner?.name || 'Unknown'}</p></td>
                        <td class="align-middle text-sm"><div class="plate-card py-1 px-2" style="font-size:0.7rem">${v.plate}</div></td>
                        <td><span class="text-secondary text-xs fw-bold">${v.color} (${v.year})</span></td>
                        <td class="align-middle text-center"><span class="text-secondary text-xs font-weight-bold">${v.type}</span></td>
                        <td><button class="btn btn-link text-danger mb-0" onclick="app.crud.delete('vehicles', ${v.id})">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button> </td>             
                    </tr>`;
                }).join('');
            },

            plates: () => {
                const grid = document.getElementById('plates-grid');
                if(!grid) return;

                grid.innerHTML = store.plates.map(p => `
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-body text-center border-0 shadow-sm">
                            <div class="plate-card mx-auto mb-2">${p.number}</div>
                            ${p.img ? `<img src="${p.img}" class="img-fluid rounded mb-2" style="height:40px; object-fit:contain;">` : ''}
                            <button class="btn btn-link text-danger btn-sm p-0" onclick="app.crud.delete('plates', ${p.id})">Remove</button>
                        </div>
                    </div>
                `).join('');
            }
        },

        modals: {
            openUserModal: () => {
                const el = document.getElementById('userModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openTypeModal: () => {
                const el = document.getElementById('typeModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openBrandModal: () => {
                const select = document.getElementById('brandTypeSelect');
                if(select) select.innerHTML = store.types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
                
                const container = document.getElementById('modelsContainer');
                if(container) container.innerHTML = '';
                
                app.crud.addModelInput();
                const el = document.getElementById('brandModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openPlateModal: () => {
                const el = document.getElementById('plateModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openVehicleModal: () => {
                const select = document.getElementById('bulkVehicleOwner');
                if(select) select.innerHTML = store.users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
                
                const tbody = document.querySelector('#bulkVehicleTable tbody');
                if(tbody) tbody.innerHTML = '';
                
                app.crud.addVehicleRow();
                const el = document.getElementById('vehicleModal');
                if(el) new bootstrap.Modal(el).show();
            },
            openTicketModal: (id) => {
                const ticket = store.tickets.find(t => t.id === id);
                if(!ticket) return;

                const user = store.users.find(u => u.id === ticket.userId) || {};
                
                dom.setText('modalUserName', user.name || '');
                dom.setText('modalUserPhone', user.phone || '');
                dom.setText('modalCategory', ticket.category || '');
                dom.setText('modalDesc', ticket.desc || '');
                
                const img = document.getElementById('modalUserImg');
                if(img) img.src = `https://ui-avatars.com/api/?name=${user.name}`;
                
                const hiddenId = document.getElementById('modalTicketId');
                if(hiddenId) hiddenId.value = ticket.id;
                
                const el = document.getElementById('ticketModal');
                if(el) {
                    new bootstrap.Modal(el).show();
                    setTimeout(() => {
                        if(window.mapInstance) window.mapInstance.remove();
                        const mapEl = document.getElementById('map');
                        if(mapEl) {
                            window.mapInstance = L.map('map').setView([ticket.lat, ticket.lng], 13);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.mapInstance);
                            L.marker([ticket.lat, ticket.lng]).addTo(window.mapInstance);
                        }
                    }, 500);
                }
            }
        },

        crud: {
            saveUser: async () => {
                const name = document.getElementById('userName')?.value;
                const email = document.getElementById('userEmail')?.value;
                const phone = document.getElementById('userPhone')?.value;
                
                if(!name) return;
                
                const data = { name, email, phone };
                try { await api.post('/api/users', data); await app.data.loadAll(); } catch(e) { console.warn('Saved locally (mock)'); store.users.push({...data, id: Date.now(), joined: '2025-02-23'}); }
                app.render.users(); app.render.dashboard();
                const el = document.getElementById('userModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            saveType: async () => {
                const name = document.getElementById('typeName')?.value;
                if(!name) return;
                
                // --- CONVERTED LOGIC: Prepare payload for the provided API ---
                const data = {
                    vehicle_categories: [name]
                };
                
                try { 
                    // Use the provided API endpoint
                    await api.post('/api/vehicle_category', data); 
                    await app.data.loadAll(); 
                    await app.data.loadTypes();
                } catch(e) { 
                    console.error('API call failed or failed to fetch all data:', e);
                    // Mock fallback
                    store.types.push({ name, id: Date.now(), icon: 'fa-circle' }); 
                }
                
                app.render.types();
                const el = document.getElementById('typeModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            addModelInput: () => {
                const div = document.createElement('div');
                div.className = 'input-group input-group-sm';
                div.innerHTML = `
                    <input type="text" class="form-control model-input" placeholder="Model Name">
                    <button class="btn btn-outline-secondary" type="button" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
                `;
                const container = document.getElementById('modelsContainer');
                if(container) container.appendChild(div);
            },
            saveBrand: async () => {
                const name = document.getElementById('brandName')?.value;
                if(!name) return;

                const data = {
                    name,
                    typeId: document.getElementById('brandTypeSelect')?.value,
                    logo: document.getElementById('brandLogo')?.value,
                    models: Array.from(document.querySelectorAll('.model-input')).map(i => i.value).filter(v => v)
                };
                try { await api.post('/api/brands', data); await app.data.loadAll(); } catch(e) { store.brands.push({...data, id: Date.now(), type_id: data.typeId}); }
                app.render.brands(); app.render.dashboard();
                const el = document.getElementById('brandModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            savePlate: async () => {
                const number = document.getElementById('plateNumber')?.value;
                if(!number) return;
                
                const data = {
                    number,
                    img: document.getElementById('plateImage')?.value
                };
                try { await api.post('/api/plates', data); await app.data.loadAll(); } catch(e) { store.plates.push({...data, id: Date.now()}); }
                app.render.plates();
                const el = document.getElementById('plateModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            updateTicket: async () => {
                const id = document.getElementById('modalTicketId')?.value;
                if(!id) return;
                
                const data = {
                    id,
                    status: document.getElementById('modalStatusSelect')?.value
                };
                try { await api.post('/api/tickets', data); await app.data.loadAll(); } catch(e) { 
                    const t = store.tickets.find(tick => tick.id == data.id); if(t) t.status = data.status;
                }
                app.render.tickets(); app.render.dashboard();
                const el = document.getElementById('ticketModal');
                if(el) bootstrap.Modal.getInstance(el).hide();
            },
            
            // Bulk Vehicle Logic
            addVehicleRow: () => {
                const tbody = document.querySelector('#bulkVehicleTable tbody');
                if(!tbody) return;
                
                const tr = document.createElement('tr');
                const typeOpts = store.types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
                tr.innerHTML = `
                    <td><select class="form-select form-select-sm type-select" onchange="app.crud.handleTypeChange(this)">
                        <option value="">Select Type</option>${typeOpts}
                    </select></td>
                    <td><select class="form-select form-select-sm brand-select" disabled onchange="app.crud.handleBrandChange(this)"><option>Select Brand</option></select></td>
                    <td><select class="form-select form-select-sm model-select" disabled><option>Select Model</option></select></td>
                    <td><input type="text" class="form-control form-control-sm" placeholder="KA 01..."></td>
                    <td><input type="text" class="form-control form-control-sm" placeholder="Color"></td>
                    <td><input type="number" class="form-control form-control-sm" placeholder="2024" style="width:70px"></td>
                    <td class="text-center"><button class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button></td>
                `;
                tbody.appendChild(tr);
            },
            handleTypeChange: (select) => {
                const row = select.closest('tr');
                const brandSelect = row.querySelector('.brand-select');
                const typeId = select.value;
                if(!typeId) { brandSelect.disabled = true; return; }
                const filteredBrands = store.brands.filter(b => b.type_id == typeId);
                brandSelect.innerHTML = '<option value="">Select Brand</option>' + 
                    filteredBrands.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
                brandSelect.disabled = false;
            },
            handleBrandChange: (select) => {
                const row = select.closest('tr');
                const modelSelect = row.querySelector('.model-select');
                const brandId = select.value;
                if(!brandId) { modelSelect.disabled = true; return; }
                const brand = store.brands.find(b => b.id == brandId);
                modelSelect.innerHTML = brand.models.map(m => `<option>${m}</option>`).join('');
                modelSelect.disabled = false;
            },
            saveBulkVehicles: async () => {
                const ownerId = document.getElementById('bulkVehicleOwner')?.value;
                const rows = document.querySelectorAll('#bulkVehicleTable tbody tr');
                const batch = [];
                
                rows.forEach(tr => {
                    const typeId = tr.querySelector('.type-select')?.value;
                    const brandId = tr.querySelector('.brand-select')?.value;
                    const model = tr.querySelector('.model-select')?.value;
                    const inputs = tr.querySelectorAll('input');
                    const plate = inputs[0]?.value;
                    const color = inputs[1]?.value;
                    const year = inputs[2]?.value;

                    if(typeId && brandId && plate) {
                        batch.push({ ownerId, typeId, brandId, model, plate, color, year });
                    }
                });
                
                if(batch.length > 0) {
                    try {
                        await api.post('/api/vehicles', batch);
                        await app.data.loadAll();
                    } catch(e) {
                        batch.forEach(v => store.vehicles.push({...v, id: Math.floor(Math.random()*10000), type: 'EV'}));
                    }
                    app.render.vehicles(); app.render.dashboard();
                    const el = document.getElementById('vehicleModal');
                    if(el) bootstrap.Modal.getInstance(el).hide();
                }
            },
            delete: async (type, id) => {
                if(confirm('Are you sure you want to delete this item?')) {
                    try {
                        await api.delete(`/api/${type}/${id}`);
                        await app.data.loadAll();
                    } catch(e) {
                        // Fallback delete
                        store[type] = store[type].filter(item => item.id != id);
                    }
                    // Re-render the specific section
                    if(type === 'users') app.render.users();
                    if(type === 'types') app.render.types();
                    if(type === 'brands') app.render.brands();
                    if(type === 'vehicles') app.render.vehicles();
                    if(type === 'plates') app.render.plates();
                    app.render.dashboard();
                }
            }
        }
    };

    // Initialize App
    document.addEventListener('DOMContentLoaded', app.init);

</script>