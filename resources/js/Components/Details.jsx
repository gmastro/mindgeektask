import React from 'react';

const styles = {
    details: "open:bg-white dark:open:bg-slate-900 dark:bg-slate-900 open:ring-1 open:ring-black/5 dark:open:ring-white/10 open:shadow-lg p-4 rounded-lg mt-2",
    summary: "text-sm leading-6 text-slate-900 dark:text-white font-semibold select-none",
    ul: "text-sm leading-6 text-slate-600 dark:text-slate-400",
    li: "flex justify-between",
}

export default function Details({ summary, details, groupId = 0, isOpen = false }) {
    const setKey = (n, delimiter = "-") => [n, groupId].join(delimiter);

    return (
        <details className={styles.details} open={isOpen}>
            <summary className={styles.summary}>{summary}</summary>
            <ul className={styles.ul}>
            {typeof(details) === 'object'
                ? Object.keys(details).map( (keyname, index) => (
                    <li key={setKey(keyname)} className={styles.li}>
                        <b>{keyname}</b>
                        <span>{details[keyname]}</span>
                    </li>
                ))
                : details.map( v => <li key={setKey(v)}>{v}</li>)
            }
            </ul>
        </details>
    );
}