import React from "react";
import { Head, Link } from "@inertiajs/react";
import Products from "@/Components/Products";
import Guest from "@/Layouts/GuestLayout";
import Pagination from "@/Components/Pagination";

export default function Index({products, type, source}) {
    const {data, ...rest} = products;
    const ucfirst = (word) => [word.charAt(0).toUpperCase(), word.slice(1).toLowerCase()].join('');

    return (
        <Guest>
            <Head title={ucfirst(type)} />
            <article>
                <header>
                    <span>From source: {source}</span>
                </header>
                <section>
                    <Pagination pages={rest} options={{boundaries: false}} />
                    <Products products={data} type={type} />
                    <Pagination pages={rest} options={{summary: false, buttons:7}}/>
                </section>
            </article>
        </Guest>
    );
}