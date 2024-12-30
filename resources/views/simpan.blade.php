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

        #dataPopup {
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

        #dataContainer {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
            /* background-color: #111111; */
        }

        #infoContainer {
            /* background-color: #111331; */
        }

        #dataPopup.open {
            left: 0;
        }

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
            /* Atur lebar logo sesuai kebutuhan */
            max-width: 100px;
            /* Atur lebar maksimum logo */
        }
    </style>
</head>

<body>
    <div id="dataPopup">
        <span class="close-popup" onclick="closePopup()">&times;</span>
        <div id="dataContainer">
            <img id="logo" src="{{asset('img/Logo_Barantin.png')}}">
            <h2>Input Data</h2>
        </div>
        <div id="infoContainer">
            <form action="/markers" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="text" name="quarantine" placeholder="Nama Karantina" required>
                <input type="text" name="commodity" placeholder="Komoditas" required>
                <input type="text" name="disease" placeholder="Nama Penyakit" required>
                <textarea name="information" placeholder="Informasi" rows="6" ;"></textarea>
                <select name="color" required>
                    <option value="red">Positif</option>
                    <option value="green">Negatif</option>
                </select>
                <input type="date" name="date_found" required>
                <input type="text" name="latitude" placeholder="Latitude" required readonly>
                <input type="text" name="longitude" placeholder="Longitude" required readonly>
                <input type="file" name="photo" accept="image/*">
                <!-- Input untuk foto -->
                <button type="submit">Tambah Marker</button>
            </form>
        </div>
    </div>
    <div id="infoOverlay">
        <span class="close-popup" onclick="closeInfoOverlay()">&times;</span>
        <h2>Information</h2>
        <div id="infoContent"></div>
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
                    html: '<div style="background-color: {{ $marker->color }}; width : 20px; height: 20px ; border-radius:50%; border: 2px solid rgb(0, 0, 0);"></div>'
                })
            }).addTo(map).bindPopup('<b>{{ $marker->quarantine }}</b><br>{{ $marker->commodity }}<br>{{ $marker->disease }}<br>{{ $marker->information }}<br>{{ $marker->date_found }}<br><img src="{{ asset('storage/' . $marker->photo_path) }}" alt="Foto" style="width:100%; height:auto;">');
            markers.push({ marker: marker, disease: '{{ $marker->disease }}', info: '<b>{{ $marker->quarantine }}</b><br>{{ $marker->commodity }}<br>{{ $marker->disease }}<br>{{ $marker->information }}<br>{{ $marker->date_found }}<br><img src="{{ asset('storage/' . $marker->photo_path) }}" alt="Foto" style="width:100%; height:auto;">' }); // Simpan marker dan informasi
        @endforeach

        // Event listener untuk menangkap klik pada peta
        map.on('click', function(e) {
            // Ambil koordinat latitude dan longitude
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            // Tampilkan koordinat di input field
            document.querySelector('input[name="latitude"]').value = lat;
            document.querySelector('input[name="longitude"]').value = lng;

            // Tampilkan popup input data
            var dataPopup = document.getElementById('dataPopup');
            dataPopup.classList.add('open'); // Tampilkan popup

            // Hapus marker terakhir jika ada
            if (lastMarker) {
                map.removeLayer(lastMarker);
            }

            // Tambahkan marker baru ke peta
            lastMarker = L.marker([lat, lng]).addTo(map);
            lastMarker.bindPopup('Koordinat: ' + lat + ', ' + lng).openPopup();
        });

        // Event listener untuk marker yang diklik
        markers.forEach(function(item) {
            item.marker.on('click', function() {
                document.getElementById('infoContent').innerHTML = item.info; // Tampilkan informasi marker
                var infoOverlay = document.getElementById('infoOverlay');
                infoOverlay.classList.add('open'); // Tampilkan overlay informasi
                item.marker.closePopup(); // Tutup popup di titik marker
                var dataPopup = document.getElementById('dataPopup');
                dataPopup.classList.remove('open'); // Tutup input data overlay
                if (lastMarker) {
                    map.removeLayer(lastMarker); // Hapus marker yang dibuat ketika mengklik titik kosong
                    lastMarker = null; // Reset lastMarker
                }
            });
        });

        // Event listener untuk mengklik peta
        map.on('click', function(e) {
            if (lastMarker) {
                map.removeLayer(lastMarker); // Hapus marker yang dibuat sebelumnya
                lastMarker = null; // Reset lastMarker
            }
            var infoOverlay = document.getElementById('infoOverlay');
            infoOverlay.classList.remove('open'); // Tutup overlay informasi
            var dataPopup = document.getElementById('dataPopup');
            dataPopup.classList.add('open'); // Tampilkan overlay input data
            // Buat marker baru di titik yang diklik
            lastMarker = L.marker(e.latlng).addTo(map);
        });

        // Fungsi untuk menutup overlay informasi
        function closeInfoOverlay() {
            var infoOverlay = document.getElementById('infoOverlay');
            infoOverlay.classList.remove('open'); // Hapus kelas 'open' untuk menyembunyikan overlay
        }

        // Fungsi untuk menutup popup
        function closePopup() {
            var dataPopup = document.getElementById('dataPopup');
            dataPopup.classList.remove('open'); // Hapus kelas 'open' untuk menyembunyikan popup
        }
    </script>
</body>

</html>