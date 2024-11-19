<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>
    <div style="display: flex;">
        <div style="background-color: aqua; width: 20%; height:100%">
            <h2>Input Data</h2>
            <select id="diseaseFilter">
                <option value="">Semua Penyakit</option>
                @foreach ($markers as $marker)
                <option value="{{ $marker->disease }}">{{ $marker->disease }}</option>
                @endforeach
            </select>
            <form action="/markers" method="POST">
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
                <button type="submit">Tambah Marker</button>
            </form>
        </div>
        <div style="margin-left: 0%; background-color:brown; width: 80%; height:100%">
            <h2>Map</h2>
            <div id="map" style="height: 600px; width: 100%;"></div>
        </div>
    </div>

    <script>
        // Inisialisasi peta
        var map = L.map('map').setView([-0.5, 113.5], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        // Variabel untuk menyimpan marker yang ditambahkan
        var currentMarker = null;

        // Menampilkan marker dari database
        @foreach ($markers as $marker)
            var marker = L.marker([{{ $marker->latitude }}, {{ $marker->longitude }}], {
                icon: L.divIcon({
                    className: 'custom-icon',
                    html: '<div style="background-color: {{ $marker->color }}; width: 20px; height: 20px; border-radius: 50%;"></div>'
                }),
                id: {{ $marker->id }} // Simpan ID marker untuk penghapusan
            }).addTo(map).bindPopup('<b>{{ $marker->quarantine }}</b><br>{{ $marker->commodity }}<br>{{ $marker->disease }}<br>{{ $marker->information }}<br>{{ $marker->date_found }}<br><button onclick="deleteMarker({{ $marker->id }})">Hapus</button> <button onclick="updateMarker({{ $marker->id }})">Update</button>');
        @endforeach

        // Event listener untuk menangkap klik pada peta
        map.on('click', function(e) {
            // Ambil koordinat latitude dan longitude
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            // Tampilkan koordinat di input field
            document.querySelector('input[name="latitude"]').value = lat;
            document.querySelector('input[name="longitude"]').value = lng;

            // Jika sudah ada marker sebelumnya, hapus marker tersebut
            if (currentMarker ) {
                map.removeLayer(currentMarker);
            }

            // Tambahkan marker baru di lokasi yang diklik
            currentMarker = L.marker([lat, lng]).addTo(map).bindPopup('Koordinat: ' + lat + ', ' + lng + '<br><button onclick="deleteNewMarker(this)">Hapus</button> <button onclick="updateNewMarker(this)">Update</button>').openPopup();
        });

        // Fungsi untuk menghapus marker
        function deleteMarker(id) {
            if (confirm("Apakah Anda yakin ingin menghapus marker ini?")) {
                var marker = markers.find(m => m.options.id === id);
                if (marker) {
                    map.removeLayer(marker);
                    markers = markers.filter(m => m !== marker);
                    fetch('/markers/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id: id })
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Marker berhasil dihapus');
                        } else {
                            console.error('Gagal menghapus marker', data);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                    });
                } else {
                    console.error('Marker tidak ditemukan');
                }
            }
        }

        // Fungsi untuk menghapus marker baru
        function deleteNewMarker(button) {
            if (confirm("Apakah Anda yakin ingin menghapus marker ini?")) {
                map.removeLayer(currentMarker);
                currentMarker = null; // Reset marker saat ini
                console.log('Marker baru berhasil dihapus');
            }
        }

        // Fungsi untuk memperbarui marker
        function updateMarker(id) {
            var marker = markers.find(m => m.options.id === id);
            if (marker) {
                var popupContent = '<form id="updateForm">' +
                    '<input type="text" name="quarantine" placeholder="Nama Karantina" required>' +
                    '<input type="text" name="commodity" placeholder="Komoditas" required>' +
                    '<input type="text" name="disease" placeholder="Nama Penyakit" required>' +
                    '<textarea name="information" placeholder="Informasi" rows="4" style="width: 90%;"></textarea>' +
                    '<select name="color" required>' +
                        '<option value="red">Positif</option>' +
                        '<option value="green">Negatif</option>' +
                    '</select>' +
                    '<input type="date" name="date_found" required>' +
                    '<button type="submit">Update</button>' +
                    '</form>';

                marker.bindPopup(popupContent).openPopup();

                // Tangani pengiriman form
                document.getElementById('updateForm').onsubmit = function(e) {
                    e.preventDefault();
                    var formData = new FormData(this);
                    var updatedData = {
                        id: id,
                        quarantine: formData.get('quarantine'),
                        commodity: formData.get('commodity'),
                        disease: formData.get('disease'),
                        information: formData.get('information'),
                        color: formData.get('color'),
                        date_found: formData.get('date_found')
                    };

                    // Kirim permintaan ke backend untuk memperbarui data marker
                    fetch('/markers/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(updatedData)
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            marker.setPopupContent('<b>' + updatedData.quarantine + '</b><br>' + updatedData.commodity + '<br>' + updatedData.disease + '<br>' + updatedData.information + '<br>' + updatedData.date_found + '<br><button onclick="deleteMarker(' + id + ')">Hapus</button> <button onclick="updateMarker(' + id + ')">Update</button>');
                            console.log('Marker berhasil diperbarui');
                        } else {
                            console.error('Gagal memperbarui marker');
                        }
                    });
                };
            }
        }

        function updateNewMarker(button) {
            var marker = currentMarker;
            var latLng = marker.getLatLng();
            var popupContent = '<form id="newUpdateForm">' +
                '<input type="text" name="quarantine" placeholder="Nama Karantina" required>' +
                '<input type="text" name="commodity" placeholder="Komoditas" required>' +
                '<input type="text" name="disease" placeholder="Nama Penyakit" required>' +
                '<textarea name="information" placeholder="Informasi" rows="4" style="width: 90%;"></textarea>' +
                '<select name="color" required>' +
                    '<option value="red"> Positif</option>' +
                    '<option value="green">Negatif</option>' +
                '</select>' +
                '<input type="date" name="date_found" required>' +
                '<button type="submit">Update</button>' +
                '</form>';

            marker.bindPopup(popupContent).openPopup();

            document.getElementById('newUpdateForm').onsubmit = function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var updatedData = {
                    latitude: latLng.lat,
                    longitude: latLng.lng,
                    quarantine: formData.get('quarantine'),
                    commodity: formData.get('commodity'),
                    disease: formData.get('disease'),
                    information: formData.get('information'),
                    color: formData.get('color'),
                    date_found: formData.get('date_found')
                };

                fetch('/markers/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(updatedData)
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          marker.setPopupContent('<b>' + updatedData.quarantine + '</b><br>' + updatedData.commodity + '<br>' + updatedData.disease + '<br>' + updatedData.information + '<br>' + updatedData.date_found + '<br><button onclick="deleteNewMarker(this)">Hapus</button> <button onclick="updateNewMarker(this)">Update</button>');
                          console.log('Marker berhasil diperbarui');
                      } else {
                          console.error('Gagal memperbarui marker');
                      }
                  });
            };
        }
    </script>
</body>

</html>