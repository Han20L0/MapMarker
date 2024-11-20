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
        }

        #dataPopup {
            position: fixed;
            top: 0;
            left: -350px;
            width: 300px;
            height: 50%;
            background-color: aqua;
            transition: left 0.3s ease;
            z-index: 1000;
            padding: 20px;
            /* border-radius: 5%; */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
        }

        #dataPopup.open {
            left: 0;
        }

        #mapContainer {
            width: 100%;
            height: 90vh;
            background-color: brown;
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        #toggleButton {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1001;
            padding: 10px;
            background-color: darkblue;
            color: white;
            border: none;
            cursor: pointer;
        }

        #filterButton {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1001;
            padding: 10px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
        }

        #mapHeader {
            background-color: chartreuse;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 50px;
        }

        .custom-icon {
            /* Anda dapat menambahkan gaya CSS untuk ikon di sini jika diperlukan */
        }

        .close-popup {
            cursor: pointer;
            color: red;
            font-weight: bold;
            float: right;
            font-size: 20px;
        }
    </style>
</head>

<body>
    <div id="dataPopup">
        <span class="close-popup" onclick="closePopup()">&times;</span> <!-- Tombol silang untuk menutup popup -->
        <h2>Input Data</h2>
        <form action="/markers" method="POST" enctype="multipart/form-data">
            <!-- Tambahkan enctype -->
            @csrf
            <input type="text" name="quarantine" placeholder="Nama Karantina" required>
            <input type="text" name="commodity" placeholder="Komoditas" required>
            <input type="text" name="disease" placeholder="Nama Penyakit" required>
            <textarea name="information" placeholder="Informasi" rows="6" style="width: 90%;"></textarea>
            <select name="color" required>
                <option value="red">Positif</option>
                <option value="green">Negatif</option>
            </select>
            <input type="date" name="date_found" required>
            <input type="text" name="latitude" placeholder="Latitude" required readonly>
            <input type="text" name="longitude" placeholder="Longitude" required readonly>
            <input type="file" name="photo" accept="image/*" required> <!-- Input untuk foto -->
            <button type="submit">Tambah Marker</button>
        </form>
    </div>
    <div id="mapContainer">
        <div id="mapHeader">
            <h2>Map Location</h2>
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
            markers.push({ marker: marker, disease: '{{ $marker->disease }}' }); // Simpan marker dan penyakitnya
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

        // Event listener untuk tombol toggle
        document.getElementById('toggleButton').addEventListener('click', function() {
            var dataPopup = document.getElementById('dataPopup');
            dataPopup.classList.toggle('open'); // Tambah atau hapus kelas 'open'
        });

        // Event listener untuk tombol filter
        document.getElementById('filterButton').addEventListener('click', function() {
            var selectedDisease = document.getElementById('diseaseFilter').value;

            // Hapus semua marker dari peta
            markers.forEach(function(item) {
                map.removeLayer(item.marker);
            });

            // Tampilkan marker yang sesuai dengan penyakit yang dipilih
            markers.forEach(function(item) {
                if (selectedDisease === "" || item.disease === selectedDisease) {
                    item.marker.addTo(map);
                }
            });
        });

        // Fungsi untuk menutup popup
        function closePopup() {
        var dataPopup = document.getElementById('dataPopup');
        dataPopup.classList.remove('open'); // Hapus kelas 'open' untuk menyembunyikan popup
        }
    </script>
</body>

</html>
