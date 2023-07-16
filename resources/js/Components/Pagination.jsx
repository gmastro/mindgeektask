import React from "react";
import { Link } from "@inertiajs/react";

export default function Pagination({ pages, styles, options }) {
    const {
        path,
        current_page,
        first_page_url,
        last_page,
        last_page_url,
        links,
        next_page_url,
        prev_page_url,
        ...rest
    } = pages;

    const literals = (attr, o = {}) => o[attr] ?? null;

    const isDisabled = (param) => param == null;

    const applyOptions = (attr) => literals(attr, options) ?? literals(attr, {
        first: <>&lt;&lt;</>,
        prev:  <>&lt;</>,
        next:  <>&gt;</>,
        last:  <>&gt;&gt;</>,
        boundaries: true,
        wrappers: true,
        summary: true,
        direction: "both",
        buttons: 5,
        query: "page",
        distinct: 'pagination',
    });

    const applyStyles = (attr) => literals(attr, styles) ?? literals(attr, {
        outer   : "flex flex-col flex-wrap shadow-md pb-2 mt-2 rounded-md",
        inner   : `flex flex-wrap mt-1 justify-center`,
        summary : "p-2 text-indigo-400 bg-gray-100 rounded-md",
        buttons : "p-2 mx-1 min-w-[3rem] text-center hover:text-orange-500 dark:hover:text-orange-500 border border-solid border-gray-300 rounded-md shadow-md",
        disabled: "p-2 mx-1 min-w-[3rem] text-center text-gray-300 dark:text-gray-300 rounded-md shadow-md cursor-not-allowed",
    });

    const boundaries = applyOptions("boundaries");
    const wrappers = applyOptions("wrappers");
    const limit = applyOptions("buttons");
    const buttonCls = applyStyles("buttons");
    const disabledCls = applyStyles("disabled");

    const summary = (content) => {
        const {per_page, from, to, total} = content;

        return (
            <div className={applyStyles("summary")}>
                Displaying {per_page} results per page. Range {from} - {to} out of {total}
            </div>
        );
    };

    const buttons = () => {
        const distinct = applyOptions("distinct");

        if (limit <= 0) {
            return (<>...</>);
        }

        var arr = [<Link key={[distinct, current_page].join("-")} className={disabledCls} disabled={true}>{current_page}</Link>];

        const pagePath  = (node)            => 
            path + "?" + [applyOptions("query"), node].join("=")
        const tagNode   = (node)            => 
            <Link key={[distinct, node].join("-")} className={buttonCls} href={pagePath(node)}>{node}</Link>
        const before    = (index, node, arr) => {
            if (applyOptions("direction") == "after") {
                return [index, node];
            }

            if (node > 1 && index <= limit) {
                ++index
                --node
                arr.unshift(tagNode(node));
            }

            return [index, node];
        }
        const after     = (index, node, arr) => {
            if (applyOptions("direction") == "before") {
                return [index, node];
            }

            if (last_page > node && index <= limit) {
                ++index;
                ++node;
                arr.push(tagNode(node));
            }

            return [index, node];
        }

        for (let i = 1, index = 1, forward = current_page, back = current_page; i < limit; [index, forward] = after(index, forward, arr), [index, back] = before(index, back, arr), i = index > i ? index : i+1);

        return (
            <>
                {arr.map( i => i )}
            </>
        );
    }

    const display = (url, compare, applied, option = true) => {
        if(option == false) {
            return;
        }

        let content = applyOptions(applied);

        return isDisabled(compare)
            ? <span className={disabledCls}>{content}</span>
            : <Link className={buttonCls} href={url}>{content}</Link>;
    };

    return (
        <div className={applyStyles("outer")}>
            {applyOptions("summary") && summary(rest)}
            <div className={applyStyles("inner")}>
                {display(first_page_url, prev_page_url, "first", boundaries)}
                {display(prev_page_url, prev_page_url, "prev", wrappers)}
                {buttons()}
                {display(next_page_url, next_page_url, "next", wrappers)}
                {display(last_page_url, next_page_url, "last", boundaries)}
            </div>
         </div>
    );
}