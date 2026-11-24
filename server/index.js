const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const { uploadsDir } = require('./database/db');

// Import routes
const authRoutes = require('./routes/auth');
const sekolahRoutes = require('./routes/sekolah');
const ujianRoutes = require('./routes/ujian');
const masterRoutes = require('./routes/master');
const persiapanRoutes = require('./routes/persiapan');
const cetakRoutes = require('./routes/cetak');
const sistemRoutes = require('./routes/sistem');
const statsRoutes = require('./routes/stats');

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(cors());
app.use(express.json({ limit: '20mb' }));
app.use(express.urlencoded({ extended: true, limit: '20mb' }));

// Static uploads directory
app.use('/uploads', express.static(uploadsDir));

// API Routes
app.use('/api/auth', authRoutes);
app.use('/api/sekolah', sekolahRoutes);
app.use('/api/ujian', ujianRoutes);
app.use('/api/master', masterRoutes);
app.use('/api/persiapan', persiapanRoutes);
app.use('/api/cetak', cetakRoutes);
app.use('/api/sistem', sistemRoutes);
app.use('/api/stats', statsRoutes);

// Health check
app.get('/api/health', (req, res) => {
  res.json({
    status: 'ok',
    app: 'Ruang Ujian',
    version: '1.0.0',
    time: new Date().toISOString()
  });
});

// Serve frontend build if exists
const clientDist = path.join(__dirname, '../client/dist');
app.use(express.static(clientDist));

// Fallback for SPA routing in Express 5
app.use((req, res) => {
  if (req.path.startsWith('/api')) {
    return res.status(404).json({ error: 'Endpoint API tidak ditemukan.' });
  }
  const indexPath = path.join(clientDist, 'index.html');
  if (fs.existsSync(indexPath)) {
    return res.sendFile(indexPath);
  }
  res.status(200).send('API Server Ruang Ujian Aktif. Silakan build frontend client.');
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Server Ruang Ujian berjalan di http://localhost:${PORT}`);
});
