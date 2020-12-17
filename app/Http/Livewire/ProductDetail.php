<?php

namespace App\Http\Livewire;

use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product, $jumlah_pesanan, $nama, $nomor;
    
    public function mount($id)
    {
        $productDetail = Product::find($id);

        if($productDetail) {
            $this->product = $productDetail;
        }
    }

    public function masukkanKeranjang()
    {
        $this->validate([
            'jumlah_pesanan' => 'required'
        ]);

        // Validasi jika user belum login saat klik masukan kerjang button maka redirect to login route
        if(!Auth::user()) {
            return redirect()->route('login');
        }

        // Menghitung total harga
        if(!empty($this->nama)) {
            $this->total_harga = ( $this->jumlah_pesanan * $this->product->harga ) + $this->product->harga_nameset;
        }else{
            $this->total_harga = $this->jumlah_pesanan * $this->product->harga;
        }

        // Cek apakah user sudah punya pesanan utama yang status nya 0
        $pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();
        
        // jika masih kosong maka kita menyimpan dan mengupdate data pesanan utama
        if(empty($pesanan)) {
            Pesanan::create([
                'user_id' => Auth::user()->id,
                'total_harga' => $this->total_harga,
                'status' => 0,
                'kode_unik' => mt_rand(100, 999)
            ]);

            // kode_pemesanan
            $pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();
            $pesanan->kode_pemesanan = 'JP'.$pesanan->id;
            $pesanan->update();

        }else{
            $pesanan->total_harga = $pesanan->total_harga + $this->total_harga;
            $pesanan->update();
        }

        // menyimpan ke pesanan details
        PesananDetail::create([
            'product_id' => $this->product->id,
            'pesanan_id' => $pesanan->id,
            'jumlah_pesanan' => $this->jumlah_pesanan,
            'nameset' => $this->nama ? true: false,
            'nama' => $this->nama,
            'nomor' => $this->nomor,
            'total_harga' => $this->total_harga
        ]);

        $this->emit('masukKeranjang');

        session()->flash('message', 'Sukses Masuk Keranjang');

        return redirect()->back();
    }

    public function render()
    {
        return view('livewire.product-detail')
        ->extends('layouts.app')
        ->section('content');
    }
}
