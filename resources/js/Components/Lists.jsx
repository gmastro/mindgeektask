import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const styles={
    div     : "flex mt-4 p-4 border rounded-r-md shadow-md shadow-indigo-300",
    label   : "w-1/4 block font-semibold",
    details : "w-2/4 flex grow justify-between items-center",
    actions : "w-1/4 flex justify-end items-center",
    link    : "mx-2 p-2 rounded-md border border-indigo-200 hover:border-indigo-500 shadow-lg shadow-indigo-200",
    button  : "mx-2 p-2 rounded-md border border-indigo-200 hover:border-indigo-500 shadow-lg shadow-indigo-200",
};

export default function Lists({ auth, contents, actions = {link: true, job: true, toggle: true} }) {
    const [form, setForm] = useState({
        job     : true,
        toggle  : true,
    });

    const ajaxCall = (data, state) => {
        setForm({...form, [state]:false});
        
        router.post(route(["products", state].join(".")), {
            method  : "post",
            data    : data
        }, {
            onError: (errors) => {
                setForm({...form, [state]:true});
                console.log(errors);
            },
            onSuccess: (res) => Promise.all([setForm({...form, [state]:true})]),
        });
    };

    return (
        <>
            {contents.map((content, _) => (
                <div key={content.source} className={styles.div}>
                    <label className={styles.label}>
                        <span className={styles?.span}>{content.id}</span>{' '}
                        {content.name}
                    </label>
                    <ul className={styles.details}>
                        <li><b>Examined</b> <i>{content.examine_counter}</i></li>
                        <li><b>Downloaded</b> <i>{content.download_counter}</i></li>
                        <li><b>Created</b> <i>{dayjs(content.created_at).fromNow()}</i></li>
                        <li><b>Updated</b> <i>{dayjs(content.updated_at).fromNow()}</i></li>
                    </ul>
                    <div className={styles.actions}>
                        {actions?.link
                            && <Link href={route(["products", content.name.toLowerCase()].join("."))}
                                className={styles.link}>View</Link>
                        }
                        {auth.user
                            && actions?.job
                            && <button type="button"
                                onClick={e => ajaxCall({id: content.id}, "job")}
                                className={styles.button}
                                disabled={!form.job}>Force Job</button>}
                        {auth.user
                            && actions?.toggle
                            && <button type="button"
                                onClick={ e => ajaxCall({id: content.id}, "toggle") }
                                className={styles.button}
                                disabled={!form.toggle}>{content.is_active ? "Deactivate" : "Activate"}</button>}
                    </div>
                </div>
            ))}
        </>
    );
}