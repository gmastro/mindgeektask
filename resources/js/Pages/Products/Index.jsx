import React from "react";
import { Head } from "@inertiajs/react";
import Products from "@/Components/Products";
import Guest from "@/Layouts/GuestLayout";

export default function Index({products, type, source}) {
    const ucfirst = (word) => [word.charAt(0).toUpperCase(), word.slice(1).toLowerCase()].join('');

    return (
        <Guest>
            <Head title={ucfirst(type)} />
            <article>
                <header>
                    <span>From source: {source}</span>
                </header>
                <section>
                    <Products products={products} type={type} />
                </section>
            </article>
        </Guest>
    );
}