import React from "react";
import { Head, Link } from "@inertiajs/react";
import Products from "@/Components/Products";
import Guest from "@/Layouts/GuestLayout";
import Pagination from "@/Components/Pagination";

export default function Index( {auth, products, type, source} ) {
    const {data, ...rest} = products;
    const ucfirst = (word) => [word.charAt(0).toUpperCase(), word.slice(1).toLowerCase()].join('');

    return (
        <Guest>
            <Head title={ucfirst(type)} />
            <article>
                <header>
                    <h2 className="text-xl">
                        {ucfirst(type)}
                        <span className="text-sm float-right"><i>Source: <Link className="text-orange-500" href={source}>{source}</Link></i></span>
                    </h2>
                </header>
                <section>
                    <Pagination pages={rest} />
                    <Products auth={auth} products={data} type={type} />
                    <Pagination pages={rest} options={{summary: false}}/>
                    {/* <Pagination pages={rest} options={{summary: false, buttons:7}}/> */}
                </section>
            </article>
        </Guest>
    );
}