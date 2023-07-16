import React from "react";
import { Head } from "@inertiajs/react";
import Guest from "@/Layouts/GuestLayout";
import NavMenu from '@/Components/Menus/NavMenu';
import { options } from '@/Components/Menus/NavMenuOptions';
import Lists from "@/Components/Lists";

const midair = "mx-auto px-4 sm:px-6 lg:px-8";
const styles = {
    nav     : `${midair} items-center flex h-16 justify-between border-b border-gray-100`,
    header  : `${midair} items-center flex h-16 justify-between`,
    h2      : "font-semibold text-xl text-gray-500 leading-tight",
 };

export default function Feeds( {auth, products} ) {
    const {data, ...rest} = products;

    return (
        <Guest>
            <Head title="Feeds" />
            <div className="w-full shadow bg-white">
                    <nav className={styles.nav}>
                        <NavMenu tag="NavLink" {...options(auth)} />
                    </nav>
                    <header className={styles.header}>
                        <h2 className={styles.h2}>Feeds</h2>
                    </header>
                </div>
            <article>
                <section>
                    <Lists auth={auth} contents={products} actions={{link:true}} />
                </section>
            </article>
        </Guest>
    );
}