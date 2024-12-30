<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f0f0f0;
        }

        #dataPopup,
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
            align-items: center;
            /* background-color: #f0f0f0; */
        }

        #h2Container {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
        }

        #dataPopup.open,
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

        button {
            background-color: #366633;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
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
        <span class="close-popup" onclick="closePopup()">&times;</span>
        <div id="h2Container">
            <img id="logo" src="{{ asset('img/Logo_Barantin.png') }}" alt="Logo">
            <h2>Input Data</h2>
        </div>
        <div id="infoContainer">
            <form action="/markers" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="text" name="quarantine" placeholder="Nama Karantina" required>
                <input type="text" name="commodity" placeholder="Komoditas" required>
                <input type="text" name="disease" placeholder="Nama Penyakit" required>
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
        <span class="close-popup" onclick="closeInfoOverlay()">&times;</span>
        <div id="h2Container">
            <img id="logo" src="{{ asset('img/Logo_Barantin.png') }}" alt="Logo">
            <h2>Information</h2>
        </div>
        <div id="infoContainer">
            <p id="infoContentQuarantine"></p>
            <p id="infoContentCommodity"></p>
            <p id="infoContentDisease"></p>
            <p id="infoContentInformation"></p>
            <p id="infoContentDateFound"></p>
            <img id="infoContentPhoto" src="" alt="Foto" style="width:100%; height:auto;">
        </div>
    </div>
    <div id="mapContainer">
        <div id="mapHeader">
            <h2>Peta Penyebaran Penyakit Barantin</h2>
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
                    document.getElementById('infoContentPhoto').src = '{{ asset('storage/photos/') }}' + markerObj.photo_path;

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

        // Event listener untuk menangkap klik pada marker
        markers.forEach(function(markerObj) {
            markerObj.marker.on('click', function() {
                // Ambil data dari marker yang diklik
                document.getElementById('infoContentQuarantine').innerText = 'Nama Karantina: ' + markerObj.quarantine;
                document.getElementById('infoContentCommodity').innerText = 'Komoditas: ' + markerObj.commodity;
                document.getElementById('infoContentDisease').innerText = 'Nama Penyakit: ' + markerObj.disease;
                document.getElementById('infoContentInformation').innerText = 'Informasi: ' + markerObj.information;
                document.getElementById('infoContentDateFound').innerText = 'Tanggal Ditemukan: ' + markerObj.date_found;
                document.getElementById('infoContentPhoto').src = '{{ asset('storage/') }}' + markerObj.photo_path;

                // Tampilkan overlay informasi
                var infoOverlay = document.getElementById('infoOverlay');
                infoOverlay.classList.add('open'); // Tampilkan overlay
            });
        });

        function closePopup() {
            document.getElementById('dataPopup').classList.remove('open');
        }

        function closeInfoOverlay() {
            document.getElementById('infoOverlay').classList.remove('open');
        }
    </script>
</body>

</html>