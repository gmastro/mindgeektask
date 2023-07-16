import React from "react";
import { Head, Link } from "@inertiajs/react";
import Products from "@/Components/Products";
import Guest from "@/Layouts/GuestLayout";
import Pagination from "@/Components/Pagination";
import NavMenu from '@/Components/Menus/NavMenu';
import { options } from '@/Components/Menus/NavMenuOptions';

const midair = "mx-auto px-4 sm:px-6 lg:px-8";
const styles = {
    nav     : `${midair} items-center flex h-16 justify-between border-b border-gray-100`,
    header  : `${midair} items-center flex h-16 justify-between`,
    h2      : "font-semibold text-xl text-gray-500 leading-tight",
 };

export default function Index( {auth, products, type, source} ) {
    const {data, ...rest} = products;
    const ucfirst = (word) => [word.charAt(0).toUpperCase(), word.slice(1).toLowerCase()].join('');

    return (
        <Guest>
            <Head title={ucfirst(type)} />
            <div className="w-full shadow bg-white">
                    <nav className={styles.nav}>
                        <NavMenu tag="NavLink" {...options(auth)} />
                    </nav>
                    <header className={styles.header}>
                        <h2 className={styles.h2}>
                            {ucfirst(type)}
                        </h2>
                        <span className="text-sm"><i>Source: <Link className="text-orange-500" href={source}>{source}</Link></i></span>
                    </header>
                </div>
            <article>
                <section>
                    <Pagination pages={rest} />
                    <Products auth={auth} products={data} type={type} />
                    <Pagination pages={rest} options={{summary: false}}/>
                </section>
            </article>
        </Guest>
    );
}