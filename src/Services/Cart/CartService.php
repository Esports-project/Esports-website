<?php
namespace App\Services\Cart;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProduitRepository;

class CartService {
    protected $session;
    protected $productRepository;
    public function __construct(SessionInterface $session, ProduitRepository $productRepository){
        $this->session=$session;
        $this->productRepository=$productRepository;
    }
    public function add(int $id) {
        $panier=$this->session->get('panier', []);
        if(!empty($panier[$id])){
            $panier[$id]++;
        }else {
            $panier[$id]=1;
        }
        $this->session->set('panier', $panier);
    }
    public function remove(int $id) {
        $panier=$this->session->get('panier', []);
        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        $this->session->set('panier', $panier);
    }

    public function getFullCart() : array {
        $panier=$this->session->get('panier', []);
        $panierWithData=[];
        foreach($panier as $id => $quantity) {
            $panierWithData[]=[
                'product'=>$this->productRepository->find($id),
                'quantity'=>$quantity
            ];
        }
        return $panierWithData;
    }



    public function getTotal() : float {
        $total=0;

        foreach($this->getFullCart() as $item){
            $totalItem=
            $total+=$item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    public function getQuantity() : int {
        $total = 0;
        foreach($this->getFullCart() as $item){
            $total = $total +1;
        }
        return $total;
    }

    public function emptyCart() {
        $panier = $this->session->get('panier', []);
        $paniervide = [];
        foreach($panier as $id => $quantity) {
            unset($panier[$id]);
        }

        $this->session->set('panier', $panier);
    }

    
}