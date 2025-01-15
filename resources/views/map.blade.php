<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Myriad+Pro:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Frutiger:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f0f0f0;
        }

        #dataPopup,
        #editPopup,
        #infoOverlay {
            position: fixed;
            top: 0;
            left: -350px;
            width: 300px;
            height: 100%;
            background-color: #142e34;
            transition: left 0.3s ease;
            z-index: 1000;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            color: white;
            overflow-y: auto;
        }

        #infoContainer {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: baseline;
            /* background-color: #f0f0f0; */
            height: 85%;
            width: 100%;

        }

        #h2Container {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
        }

        #filterContainer {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 20%;
        }

        #dataPopup.open,
        #editPopup.open,
        #infoOverlay.open {
            left: 0;
        }

        #mapContainer {
            width: 100%;
            height: 90vh;
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        #toggleButton {
            position: absolute;
            top: 10px;
            z-index: 1001;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            transition: background-color 0.3s;
            left: 10px;
            background-color: #2980b9;
        }

        #toggleButton:hover {
            opacity: 0.8;
        }

        #mapHeader {
            background-color: #366633;
            color: white;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 50px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .close-popup {
            cursor: pointer;
            color: red;
            font-weight: bold;
            float: right;
            font-size: 20px;
        }

        #buttons-container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        #buttons {
            width: 40%;
        }

        #photo-container {
            display: flex;
            width: 100%;
            height: 35%;
            background-color: #2980b9;
            margin-bottom: 10px;
        }

        h2 {
            margin: 0;
            font-size: 1.5em;
        }

        input,
        textarea,
        select {
            width: 90%;
            padding: 10px;
            margin: 5px 0;
            border: none;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 24px;
            color: #f0f0f0;
            position: fixed;
        }

        h3 {
            font-family: 'Manrope', sans-serif;
            font-size: 24px;
            color: #f0f0f0;
        }

        h5 {
            font-family: 'Manrope', sans-serif;
            color: #f0f0f0;
            font-size: 10px;
            padding-left: 70%;
        }

        button {
            background-color: #366633;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        button:hover {
            background-color: #1f6391;
        }

        #logo {
            display: block;
            margin-right: 30px;
            margin-bottom: 10px;
            width: 20%;
            max-width: 100px;
        }
    </style>
</head>

<body>
    <div id="dataPopup">
        <span class="close-popup" onclick="closeAllPopups()">&times;</span>
        <div id="h2Container">
            <img id="logo" src="{{ asset('img/Logo_Barantin.png') }}" alt="Logo">
            <h3>Input Data</h3>
        </div>
        <div id="infoContainer">
            <form action="/markers" method="POST" enctype="multipart/form-data">
                @csrf
                <select name="quarantine" id="quarantineSelect" required onchange="updateDiseaseOptions()">
                    <option value="">Pilih Karantina</option>
                    <option value="Karantina Hewan">Karantina Hewan</option>
                    <option value="Karantina Ikan">Karantina Ikan</option>
                    <option value="Karantina Tumbuhan">Karantina Tumbuhan</option>
                </select>
                <select name="disease" id="diseaseSelect" required>
                    <option value="">Pilih Penyakit</option>
                </select>
                <input type="text" name="commodity" placeholder="Komoditas" required>
                <textarea name="information" placeholder="Informasi" rows="6"></textarea>
                <select name="color" required>
                    <option value="red">Positif</option>
                    <option value="green">Negatif</option>
                </select>
                <input type="date" name="date_found" required>
                <input type="text" name="latitude" placeholder="Latitude" required readonly>
                <input type="text" name="longitude" placeholder="Longitude" required readonly>
                <input type="file" name="photo" accept="image/*">
                <button type="submit">Tambah Marker</button>
            </form>
        </div>
    </div>
    <div id="infoOverlay">
        <span class="close-popup" onclick="closeAllPopups()">&times;</span>
        <div id="h2Container">
            <img id="logo" src="{{ asset('img/Logo_Barantin.png') }}" alt="Logo">
            <h3>Information</h3>
        </div>
        <div id="infoContainer">
            <p id="infoContentQuarantine"></p>
            <p id="infoContentCommodity"></p>
            <p id="infoContentDisease"></p>
            <p id="infoContentInformation"></p>
            <p id="infoContentDateFound"></p>
            <div id="photo-container">
                <img id="infoContentPhoto" src="" alt="Foto" style="width:100%; height:auto;">
            </div>
            <div id="buttons-container">
                <form id="deleteForm" action="{{ route('markers.destroy') }}" method="POST" style="display:inline;"
                    onsubmit="return confirmDelete(event, this);">
                    @csrf
                    <input type="hidden" name="id" id="deleteMarkerId">
                    <button type="submit">Hapus Marker</button>
                </form>
                <button id="editButton" onclick="openEditPopup()">Edit Marker</button>
            </div>
        </div>
    </div>
    <div id="editPopup">
        <span class="close-popup" onclick="closeAllPopups()">&times;</span>
        <div id="h2Container">
            <img id="logo" src="{{ asset('img/Logo_Barantin.png') }}" alt="Logo">
            <h3>Edit Data Marker</h3>
        </div>
        <form id="editForm" action="/markers/update" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="editMarkerId">
            <select name="quarantine" id="editQuarantineSelect" required onchange="updateDiseaseOptionsEdit()">
                <option value="">Pilih Karantina</option>
                <option value="Karantina Hewan">Karantina Hewan</option>
                <option value="Karantina Ikan">Karantina Ikan</option>
                <option value="Karantina Tumbuhan">Karantina Tumbuhan</option>
            </select>
            <select name="disease" id="editDiseaseSelect" required>
                <option value="">Pilih Penyakit</option>
            </select>
            <input type="text" name="commodity" id="editCommodity" placeholder="Komoditas" required>
            <textarea name="information" id="editInformation" placeholder="Informasi" rows="6"></textarea>
            <select name="color" id="editColor" required>
                <option value="red">Positif</option>
                <option value="green">Negatif</option>
            </select>
            <input type="date" name="date_found" id="editDateFound" required>
            <input type="file" name="photo" accept="image/*">
            <button type="submit">Update Marker</button>
        </form>
    </div>
    <div id="mapContainer">
        <div id="mapHeader">
            <h2>Peta Penyebaran Penyakit Barantin</h2>
            <h5>Filter Berdasarkan :</h5>
            <div id="filterContainer">
                <select id="combinedFilter" onchange="filterMarkers()">
                    <option value="">Tampilkan Semua</option>
                    <option value="Karantina Hewan, Notifiable Avian Influenza">Karantina Hewan, Notifiable Avian
                        Influenza
                    </option>
                    <option value="Karantina Hewan, Rabies">Karantina Hewan, Rabies</option>
                    <option value="Karantina Hewan, Foot and Mouth Disease">Karantina Hewan, Foot and Mouth Disease
                    </option>
                    <option value="Karantina Hewan, Brucellosis">Karantina Hewan, Brucellosis</option>
                    <option value="Karantina Hewan, Lumpy Skin Disease">Karantina Hewan, Lumpy Skin Disease</option>
                    <option value="Karantina Hewan, African Swine Fever">Karantina Hewan, African Swine Fever</option>
                    <option value="Karantina Ikan, AHPND (Acute Hepatopancreatic Necrosis Disease)">Karantina Ikan,
                        AHPND
                    </option>
                    <option value="Karantina Ikan, TSV (Infection with Taura Syndrome Virus)">Karantina Ikan, TSV
                    </option>
                    <option value="Karantina Ikan, WSSV (Infection with White Spot Syndrome Virus)">Karantina Ikan, WSSV
                    </option>
                    <option
                        value="Karantina Ikan, VER / VNN (Viral Encephalopathy and Retinopathy / Viral Nervous Necrosis)">
                        Karantina Ikan, VER / VNN</option>
                    <option
                        value="Karantina Ikan, Infection with Macrobachium Rosenbergii Nodavirus (White Tail Disease)">
                        Karantina
                        Ikan, White Tail Disease</option>
                    <option value="Karantina Tumbuhan, South American Leaf Blight">Karantina Tumbuhan, South American
                        Leaf
                        Blight
                    </option>
                    <option value="Karantina Tumbuhan, Red Palm Weevil">Karantina Tumbuhan, Red Palm Weevil</option>
                    <option value="Karantina Tumbuhan, Cadang-cadang Viroid">Karantina Tumbuhan, Cadang-cadang Viroid
                    </option>
                    <option value="Karantina Tumbuhan, Lethal Yellowing">Karantina Tumbuhan, Lethal Yellowing</option>
                    <option value="Karantina Tumbuhan, Mediterranean Fruit Fly">Karantina Tumbuhan, Mediterranean Fruit
                        Fly
                    </option>
                </select>
            </div>
        </div>
        <div id="map"></div>
    </div>

    <script>
        // Inisialisasi peta
        var map = L.map('map', {zoomControl:false}).setView([-0.5, 113.5], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        // Tambahkan kontrol zoom di sebelah kanan
        L.control.zoom({
            position: 'topright' // Posisi kontrol zoom di kanan atas
        }).addTo(map);

        // Variabel untuk menyimpan marker yang ditambahkan
        var markers = []; // Array untuk menyimpan semua marker
        var lastMarker; // Variabel untuk menyimpan marker terakhir yang ditambahkan

        // Menampilkan marker dari database
        @foreach ($markers as $marker)
            var marker = L.marker([{{ $marker->latitude }}, {{ $marker->longitude }}], {
            icon: L.divIcon({
                className: 'custom-icon',
                html: '<div style="background-color: {{ $marker->color }}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid rgb(0, 0, 0);"></div>'
                })
            }).addTo(map);

            markers.push({
                id: '{{ $marker->id }}', // Tambahkan ID marker
                quarantine: '{{ $marker->quarantine }}',
                commodity: '{{ $marker->commodity }}',
                disease: '{{ $marker->disease }}',
                information: '{{ $marker->information }}',
                date_found: '{{ $marker->date_found }}',
                photo_path: '{{ $marker->photo_path }}',
                marker: marker
            }); // Simpan marker dan informasi

            // Event listener untuk marker yang diklik
            markers.forEach(function(markerObj) {
                markerObj.marker.on('click', function() {
                    // Tutup popup edit jika terbuka
                    closeEditPopup();
                    // Hapus marker terakhir jika ada
                    if (lastMarker) {
                        map.removeLayer(lastMarker); // Hapus marker yang dibuat sebelumnya
                        lastMarker = null; // Reset lastMarker
                    }
                    // Ambil data dari marker yang diklik
                    document.getElementById('infoContentQuarantine').innerText = 'Nama Karantina: ' + markerObj.quarantine;
                    document.getElementById('infoContentCommodity').innerText = 'Komoditas: ' + markerObj.commodity;
                    document.getElementById('infoContentDisease').innerText = 'Nama Penyakit: ' + markerObj.disease;
                    document.getElementById('infoContentInformation').innerText = 'Informasi: ' + markerObj.information;
                    document.getElementById('infoContentDateFound').innerText = 'Tanggal Ditemukan: ' + markerObj.date_found;
                    document.getElementById('infoContentPhoto').src = '{{ asset('storage') }}/' + markerObj.photo_path;

                    // Set ID marker yang akan dihapus
                    document.getElementById('deleteMarkerId').value = markerObj.id;


                    // Tampilkan overlay informasi
                    var infoOverlay = document.getElementById('infoOverlay');
                    infoOverlay.classList.add('open'); // Tampilkan overlay
                });
            });
        @endforeach

        // Event listener untuk mengklik peta
        map.on('click', function(e) {
            // Tutup overlay informasi jika terbuka
            var infoOverlay = document.getElementById('infoOverlay');
            if (infoOverlay.classList.contains('open')) {
                infoOverlay.classList.remove('open'); // Tutup overlay informasi
            }

            // Tutup popup edit jika terbuka
            closeEditPopup();

            // Ambil koordinat latitude dan longitude
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            // Tampilkan koordinat di input field
            document.querySelector('input[name="latitude"]').value = lat;
            document.querySelector('input[name="longitude"]').value = lng;

            // Tampilkan popup input data
            var dataPopup = document.getElementById('dataPopup');
            dataPopup.classList.add('open'); // Tampilkan overlay input data

            // Hapus marker terakhir jika ada
            if (lastMarker) {
                map.removeLayer(lastMarker); // Hapus marker yang dibuat sebelumnya
                lastMarker = null; // Reset lastMarker
            }

            // Buat marker baru di titik yang diklik
            lastMarker = L.marker(e.latlng).addTo(map);
        });

        function updateDiseaseOptions() {
            var quarantineSelect = document.getElementById('quarantineSelect');
            var diseaseSelect = document.getElementById('diseaseSelect');
            var selectedQuarantine = quarantineSelect.value;

            // Kosongkan pilihan penyakit
            diseaseSelect.innerHTML = '<option value="">Pilih Penyakit</option>';

            // Tambahkan pilihan penyakit berdasarkan pilihan karantina
            if (selectedQuarantine === 'Karantina Hewan') {
                diseaseSelect.innerHTML += '<option value="Notifiable Avian Influenza">Notifiable Avian Influenza</option>';
                diseaseSelect.innerHTML += '<option value="Rabies">Rabies</option>';
                diseaseSelect.innerHTML += '<option value="Foot and Mouth Disease">Foot and Mouth Disease</option>';
                diseaseSelect.innerHTML += '<option value="Brucellosis">Brucellosis</option>';
                diseaseSelect.innerHTML += '<option value="Lumpy Skin Disease">Lumpy Skin Disease</option>';
                diseaseSelect.innerHTML += '<option value="African Swine Fever">African Swine Fever</option>';
            } else if (selectedQuarantine === 'Karantina Ikan') {
                diseaseSelect.innerHTML += '<option value="AHPND (Acute Hepatopancreatic Necrosis Disease)">AHPND (Acute Hepatopancreatic Necrosis Disease)</option>';
                diseaseSelect.innerHTML += '<option value="TSV (Infection with Taura Syndrome Virus)">TSV (Infection with Taura Syndrome Virus)</option>';
                diseaseSelect.innerHTML += '<option value="WSSV (Infection with White Spot Syndrome Virus)">WSSV (Infection with White Spot Syndrome Virus)</option>';
                diseaseSelect.innerHTML += '<option value="VER / VNN  (Viral Encephalopathy and Retinopathy / Viral Nervous Necrosis)">VER / VNN (Viral Encephalopathy and Retinopathy / Viral Nervous Necrosis)</option>';
                diseaseSelect.innerHTML += '<option value="Infection with Macrobachium Rosenbergii Nodavirus (White Tail Disease)">Infection with Macrobachium Rosenbergii Nodavirus (White Tail Disease)</option>';
            } else if (selectedQuarantine === 'Karantina Tumbuhan') {
                diseaseSelect.innerHTML += '<option value="South American Leaf Blight">South American Leaf Blight</option>';
                diseaseSelect.innerHTML += '<option value="Red Palm Weevil">Red Palm Weevil</option>';
                diseaseSelect.innerHTML += '<option value="Cadang-cadang Viroid">Cadang-cadang Viroid</option>';
                diseaseSelect.innerHTML += '<option value="Lethal Yellowing">Lethal Yellowing</option>';
                diseaseSelect.innerHTML += '<option value="Mediterranean Fruit Fly">Mediterranean Fruit Fly</option>';
            }
        }

        function updateDiseaseOptionsEdit() {
            var quarantineSelect = document.getElementById('editQuarantineSelect');
            var diseaseSelect = document .getElementById('editDiseaseSelect');
            var selectedQuarantine = quarantineSelect.value;

            // Kosongkan pilihan penyakit sebelumnya
            diseaseSelect.innerHTML = '<option value="">Pilih Penyakit</option>';

            // Tambahkan pilihan penyakit berdasarkan karantina yang dipilih
            if (selectedQuarantine === 'Karantina Hewan') {
                diseaseSelect.innerHTML += '<option value="Notifiable Avian Influenza">Notifiable Avian Influenza</option>';
                diseaseSelect.innerHTML += '<option value="Rabies">Rabies</option>';
                diseaseSelect.innerHTML += '<option value="Foot and Mouth Disease">Foot and Mouth Disease</option>';
                diseaseSelect.innerHTML += '<option value="Brucellosis">Brucellosis</option>';
                diseaseSelect.innerHTML += '<option value="Lumpy Skin Disease">Lumpy Skin Disease</option>';
                diseaseSelect.innerHTML += '<option value="African Swine Fever">African Swine Fever</option>';
            } else if (selectedQuarantine === 'Karantina Ikan') {
                diseaseSelect.innerHTML += '<option value="AHPND (Acute Hepatopancreatic Necrosis Disease)">AHPND (Acute Hepatopancreatic Necrosis Disease)</option>';
                diseaseSelect.innerHTML += '<option value="TSV (Infection with Taura Syndrome Virus)">TSV (Infection with Taura Syndrome Virus)</option>';
                diseaseSelect.innerHTML += '<option value="WSSV (Infection with White Spot Syndrome Virus)">WSSV (Infection with White Spot Syndrome Virus)</option>';
                diseaseSelect.innerHTML += '<option value="VER / VNN  (Viral Encephalopathy and Retinopathy / Viral Nervous Necrosis">VER / VNN (Viral Encephalopathy and Retinopathy / Viral Nervous Necrosis)</option>';
                diseaseSelect.innerHTML += '<option value="Infection with Macrobachium Rosenbergii Nodavirus (White Tail Disease ">Infection with Macrobachium Rosenbergii Nodavirus (White Tail Disease)</option>';
            } else if (selectedQuarantine === 'Karantina Tumbuhan') {
                diseaseSelect.innerHTML += '<option value="South American Leaf Blight">South American Leaf Blight</option>';
                diseaseSelect.innerHTML += '<option value="Red Palm Weevil">Red Palm Weevil</option>';
                diseaseSelect.innerHTML += '<option value="Cadang-cadang Viroid">Cadang-cadang Viroid</option>';
                diseaseSelect.innerHTML += '<option value="Lethal Yellowing">Lethal Yellowing</option>';
                diseaseSelect.innerHTML += '<option value="Mediterranean Fruit Fly">Mediterranean Fruit Fly</option>';
            }
        }

        function filterMarkers() {
            var combinedFilter = document.getElementById('combinedFilter').value;

            // Ambil karantina dan penyakit dari pilihan
            var [quarantineFilter, diseaseFilter] = combinedFilter.split(', ').map(item => item.trim());

            markers.forEach(function(markerObj) {
                var marker = markerObj.marker;
                var showMarker = true;

                // Filter berdasarkan karantina
                if (quarantineFilter && markerObj.quarantine !== quarantineFilter) {
                    showMarker = false;
                }

                // Filter berdasarkan penyakit
                if (diseaseFilter && markerObj.disease !== diseaseFilter) {
                    showMarker = false;
                }

                // Tampilkan atau sembunyikan marker
                if (showMarker) {
                    marker.addTo(map); // Tampilkan marker
                } else {
                    map.removeLayer(marker); // Sembunyikan marker
                }
            });
        }

        function confirmDelete(event, form) {
            event.preventDefault(); // Mencegah form dari pengiriman default

            // Ambil ID marker yang akan dihapus
            var markerId = form.querySelector('#deleteMarkerId').value;

            // Temukan marker yang sesuai dengan ID
            var markerToDelete = markers.find(markerObj => markerObj.id === markerId);

            if (markerToDelete) {
                // Hapus marker dari peta
                map.removeLayer(markerToDelete.marker);

                // Hapus marker dari array markers
                markers = markers.filter(markerObj => markerObj.id !== markerId);

                // Kirim form untuk menghapus data dari server
                form.submit();
            } else {
                alert('Marker tidak ditemukan.');
            }
        }

        function openEditPopup() {
            var markerId = document.getElementById('deleteMarkerId').value;
            var markerToEdit = markers.find(markerObj => markerObj.id === markerId);

            if (markerToEdit) {
                // Isi form edit dengan data marker
                document.getElementById('editMarkerId').value = markerToEdit.id;
                document.getElementById('editQuarantineSelect').value = markerToEdit.quarantine;
                document.getElementById('editCommodity').value = markerToEdit.commodity;
                document.getElementById('editInformation').value = markerToEdit.information;
                document.getElementById('editDateFound').value = markerToEdit.date_found;

                // Tampilkan popup edit
                document.getElementById('editPopup').classList.add('open');

                // Panggil fungsi untuk memperbarui pilihan penyakit berdasarkan karantina yang dipilih
                updateDiseaseOptionsEdit();

                document.getElementById('editDiseaseSelect').value = markerToEdit.disease;

                document.getElementById('editColor').value = markerToEdit.color;
            } else {
                alert('Marker tidak ditemukan untuk diedit.');
            }
        }

        function closeAllPopups() {
            // Tutup semua popup
            closePopup(); // Menutup dataPopup
            closeInfoOverlay(); // Menutup infoOverlay
            closeEditPopup(); // Menutup editPopup

            // Hapus marker terakhir jika ada
            if (lastMarker) {
            map.removeLayer(lastMarker); // Hapus marker yang dibuat sebelumnya
            lastMarker = null; // Reset lastMarker
            }
        }

        function closePopup() {
            document.getElementById('dataPopup').classList.remove('open');
        }

        function closeInfoOverlay() {
            document.getElementById('infoOverlay').classList.remove('open');
        }

        function closeEditPopup() {
            document.getElementById('editPopup').classList.remove('open');
        }
    </script>
</body>

</html>
