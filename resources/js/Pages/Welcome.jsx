import { Link, Head } from '@inertiajs/react';
import NavMenu from '@/Components/Menus/NavMenu';
import { options } from '@/Components/Menus/NavMenuOptions';

const midair = "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8";
const styles = {
    nav     : `${midair} items-center flex h-16 justify-between border-b border-gray-100`,
    header  : `${midair} items-center flex h-16`,
    h2      : "font-semibold text-xl text-gray-500 leading-tight",
    h3      : "font-semibold text-lg text-white leading-tight pb-2",
    main    : "min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white",
    article : `${midair} text-sm text-gray-300 dark:text-gray-200 py-8`,
    section : "p-4 mb-4 rounded-md shadow-sm shadow-indigo-400",
    ol      : "list-decimal pl-4 mt-4",
    footer  : "fixed bottom-0 py-2 w-full text-sm text-gray-500 dark:text-gray-400 text-right sm:text-right",
};

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    return (
        <>
            <Head title="Welcome" />
            <main className={styles.main}>
                <div className="w-full shadow bg-white">
                    <nav className={styles.nav}>
                        <NavMenu tag="NavLink" {...options(auth)} />
                    </nav>
                    <header className={styles.header}>
                        <h2 className={styles.h2}>Welcome</h2>
                    </header>
                </div>
                <article className={styles.article}>
                  <section className={styles.section}>
                        <header><h3 className={styles.h3}>Disclaimer</h3></header>
                        <p className="mt-4 p-4 bg-orange-600 rounded-md">Default content downloaded and processed originates from <Link href="https://pornhub.com" className="font-semibold text-indigo-800 dark:text-indigo-800 text-shadow">https://pornhub.com</Link>. That means <b>adult content and nudity</b></p>
                        <p className="mt-4">Reasons for using this content -besides it was part of a task- it floods the source with enough requests for retrieving it, once obtained processing it and finally displaying it.</p>
                        <p className="mt-4">This is a Work-In-Progress (WIP)</p>
                    </section>
                    <section className={styles.section}>
                        <header><h3 className={styles.h3}>Usage</h3></header>
                        <ol className={styles.ol}>
                            <li>
                                Initialize docker<br />
                                <code className="bg-white rounded text-orange-500 dark:text-orange-500">./vendor/bin/sail up -d</code>
                            </li>
                            <li>
                                Seed the database<br />
                                <code className="bg-white rounded text-orange-500 dark:text-orange-500">./vendor/bin/sail artisan migrate --seed</code>
                            </li>
                            <li>All links are located into relational database table <i className="font-bold">remote_feeds</i></li>
                            <li>
                                Once migration is completed, none of the jobs will be performed unless you start the scheduler. To do so;<br />
                                <code className="bg-white rounded text-orange-500 dark:text-orange-500">* * * * * cd /path-to-your-project &amp;&amp; ./vendor/bin/sail schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
                            </li>
                            <li>
                                To preview all background jobs whilst scheduler is active, run<br />
                                <code className="bg-white rounded text-orange-500 dark:text-orange-500">./vendor/bin/sail artisan queue:list</code>
                            </li>
                            <li>Keep default <i>./storage/logs/laravel.log</i> file open to examine messages from all processes</li>
                        </ol>
                        <p className="mt-4">
                            Other options via {' '}
                            <Link href={route("dashboard")} className="font-semibold text-orange-500 dark:text-orange-500">Dashboard</Link>{' '}
                            -once you've created an account and have signed in;
                        </p>
                        <ol className={styles.ol}>
                            <li>Force seed the database with the contents from any listed feed located into relational database table <i className="font-bold">remote_feeds</i></li>
                        </ol>
                    </section>
                    <section className={styles.section}>
                        <header><h3 className={styles.h3}>Todo</h3></header>
                        <ol>
                            <li className="flex">
                                <span className="font-bold w-1/4">Dispatch all remote feeds via batch</span>
                                <span className="italic grow w-3/4">Currently each separate job runs without a flow, but when all jobs located into <i>remote_feeds</i> table, added into <i>Bus::Batch</i> return an exception due to missing batch identifier</span>
                            </li>
                            <li className="flex">
                                <span className="font-bold w-1/4">Verify Uniqueness per worker</span>
                                <span className="italic grow w-3/4">Currently the system is tested with a single worker. Need to test retrieval with multiple workers</span>
                            </li>
                            <li className="flex mt-2">
                                <span className="font-bold w-1/4">Automate the process</span>
                                <span className="italic grow w-3/4">Set an interval for processing the content within the scheduler</span>
                            </li>
                        </ol>
                    </section>
                    <section className={styles.section + " mb-0"}>
                        <header><h3 className={styles.h3}>Some RnD</h3></header>
                        <ol>
                            <li className="flex">
                                <span className="font-bold w-1/4">Add search filters</span>
                                <span className="italic grow w-3/4">Per rendered collection via Inertia add filters</span>
                            </li>
                            <li className="flex">
                                <span className="font-bold w-1/4">Database Per Product</span>
                                <span className="italic grow w-3/4">This might not be the best of ideas, but it isolates content from different sources</span>
                            </li>
                            <li className="flex mt-2">
                                <span className="font-bold w-1/4">Replace Laravel Queues</span>
                                <span className="italic grow w-3/4">Due to the fact that Laravel queues are only good for development, for production it would be better to use RabbitMQ</span>
                            </li>
                            <li className="flex mt-2">
                                <span className="font-bold w-1/4">Websockets</span>
                                <span className="italic grow w-3/4">Add websockets to display processes status via dashboard</span>
                            </li>
                        </ol>
                    </section>
                </article>
            </main>
            <footer className={styles.footer}>
                Laravel v{laravelVersion} (PHP v{phpVersion})
            </footer>
        </>
    );
}
