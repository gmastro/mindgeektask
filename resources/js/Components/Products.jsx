import React from 'react';
import Product from "@/Components/Product";

export default function Products({ products, type = 'pornstars' }) {
    const ucfirst = (word) => [word.charAt(0).toUpperCase(), word.slice(1).toLowerCase()].join('');
    
    return (
        <>
            <header>
                <h2>{ucfirst(type)}</h2>
            </header>
            <div>
                {products.map((product) => <Product key={[type, product.id].join('-')} product={product} />)}
            </div>
        </>
    );
}