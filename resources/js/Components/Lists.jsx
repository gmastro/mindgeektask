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
            onSuccess: (res) => Promise.all([console.log(res), setForm({...form, [state]:true})]),
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
                        <li><b>Examined</b> {content.examine_counter}</li>
                        <li><b>Downloaded</b> {content.download_counter}</li>
                        <li><b>Created</b> {dayjs(content.created_at).fromNow()}</li>
                        <li><b>Updated</b> {dayjs(content.updated_at).fromNow()}</li>
                    </ul>
                    <div className={styles.actions}>
                        {actions?.link
                            && <Link href={route(["products", content.name.toLowerCase()].join("."))} className={styles.link}>View</Link>}
                        {auth.user
                            && actions?.job
                            && <button type="button" onClick={e => ajaxCall({id: content.id}, "job")}
                                className={styles.button}
                                disabled={!form.job}>Force Job</button>}
                        {auth.user
                            && actions?.toggle
                            && <button type="button" onClick={e => ajaxCall({id: content.id}, "toggle")}
                                className={styles.button}
                                disabled={!form.toggle}>{contents.is_active ? "Deactivate" : "Activate"}</button>}
                    </div>
                </div>
            ))}
        </>
    );
}