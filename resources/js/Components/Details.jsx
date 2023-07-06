import React from 'react';

export default function Details({ summary, details, groupId, isOpen }) {

    let detailsClassName="open:bg-white dark:open:bg-slate-900 open:ring-1 open:ring-black/5 dark:open:ring-white/10 open:shadow-lg p-6 rounded-lg",
        summaryClassName="text-sm leading-6 text-slate-900 dark:text-white font-semibold select-none",
        ulClassName="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400";

    const isObject = typeof(details) === 'object';

    let inner = (
        <>
            <summary className={summaryClassName}><b>{summary}</b></summary>
            <ul className={ulClassName}>
            {isObject
                ? Object.keys(details).map( (keyname, index) => <li key={[keyname, groupId].join("-")}>{keyname}: {details[keyname]}</li>)
                : details.map( v => <li key={[v, groupId].join("-")}>{v}</li>)
            }
            </ul>
        </>
    )

    return isOpen
        ?  <details className={detailsClassName} open>{inner}</details>
        :  <details className={detailsClassName}>{inner}</details>
}