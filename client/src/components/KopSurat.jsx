import React from 'react';
import { useApp } from '../context/AppContext';

export default function KopSurat({ customSekolah }) {
  const { sekolah: globalSekolah } = useApp();
  const sekolah = customSekolah || globalSekolah;

  if (!sekolah) return null;

  return (
    <div className="kop-surat mb-6 text-slate-900 select-text">
      <div className="flex items-center justify-between gap-4 pb-2">
        {/* Logo Sekolah */}
        <div className="w-24 h-24 shrink-0 flex items-center justify-center">
          {sekolah.logo_url ? (
            <img 
              src={sekolah.logo_url} 
              alt="Logo Sekolah" 
              className="max-w-full max-h-full object-contain"
            />
          ) : (
            <img 
              src="/icon.svg" 
              alt="Logo Default" 
              className="w-20 h-20 object-contain"
            />
          )}
        </div>

        {/* Identitas Teks Kop */}
        <div className="flex-1 text-center font-serif leading-tight">
          {sekolah.kop_baris1 && (
            <p className="text-xs sm:text-sm font-semibold tracking-wider uppercase text-slate-800">
              {sekolah.kop_baris1}
            </p>
          )}
          {sekolah.kop_baris2 && (
            <p className="text-xs sm:text-sm font-semibold tracking-wider uppercase text-slate-800">
              {sekolah.kop_baris2}
            </p>
          )}
          {sekolah.kop_baris3 && (
            <p className="text-xs sm:text-sm font-medium tracking-wide uppercase text-slate-700">
              {sekolah.kop_baris3}
            </p>
          )}
          <h1 className="text-base sm:text-xl font-bold tracking-tight uppercase text-black mt-0.5">
            {sekolah.nama_sekolah}
          </h1>
          {sekolah.npsn && (
            <p className="text-[11px] font-medium text-slate-700">
              NPSN: {sekolah.npsn} {sekolah.nss ? `| NSS: ${sekolah.nss}` : ''}
            </p>
          )}
          <p className="text-[11px] sm:text-xs text-slate-700 mt-0.5">
            {sekolah.alamat}
            {sekolah.desa_kelurahan ? `, ${sekolah.desa_kelurahan}` : ''}
            {sekolah.kecamatan ? `, Kec. ${sekolah.kecamatan}` : ''}
            {sekolah.kabupaten_kota ? `, ${sekolah.kabupaten_kota}` : ''}
            {sekolah.kode_pos ? ` ${sekolah.kode_pos}` : ''}
          </p>
          <p className="text-[10px] sm:text-[11px] text-slate-600">
            {sekolah.telepon ? `Telp: ${sekolah.telepon}` : ''}
            {sekolah.telepon && sekolah.email ? ' | ' : ''}
            {sekolah.email ? `Email: ${sekolah.email}` : ''}
            {sekolah.website ? ` | Laman: ${sekolah.website}` : ''}
          </p>
        </div>

        {/* Space balancer on right for print alignment */}
        <div className="w-16 shrink-0 hidden sm:block"></div>
      </div>

      {/* Garis Pembatas Kop Surat Resmi (Garis Ganda Tebal-Tipis) */}
      <div className="border-b-[3px] border-black mt-1"></div>
      <div className="border-b-[1px] border-black mt-[1.5px]"></div>
    </div>
  );
}
