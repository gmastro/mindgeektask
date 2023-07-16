import React from 'react';
import Product from "@/Components/Product";

export default function Products({ auth, products, type = 'pornstars' }) {
    return (
        <>
            <div className="grid grid-cols-4 mt-2 rounded-md shadow-md">
                {products.map((product) => <Product auth={auth} key={[type, product.id].join('-')} product={product} />)}
            </div>
        </>
    );
}