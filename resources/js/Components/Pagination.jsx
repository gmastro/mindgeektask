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

    const isDisabled = (param) => param === null;

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

    const boundaries = applyOptions("boundaries");
    const wrappers = applyOptions("wrappers");
    const limit = applyOptions("buttons");
    // const grids = (boundaries ? 2 : 0) + (wrappers ? 2 : 0) + (limit > 0 ? limit : 0);

    const applyStyles = (attr) => literals(attr, styles) ?? literals(attr, {
        outer   : "flex flex-col flex-wrap shadow-md pb-2 mt-2 rounded-md",
        inner   : `flex flex-wrap mt-1 justify-center`,
        // within  : limit > 0 ? `grid grid-cols-${limit} gap-1` : "",
        summary : "bg-gray-100 text-indigo-400 rounded-md p-2",
        buttons : "border border-solid border-gray-300 rounded-md p-2 hover:text-orange-500 shadow-md text-center mx-1",
        disabled: "text-gray rounded-md p-2 shadow-md text-center mx-1",
    });

    const buttonCls = applyStyles("buttons");

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

        var arr = [<Link key={[distinct, current_page].join("-")} className={applyStyles("disabled")} disabled={true}>{current_page}</Link>];

        const pagePath  = (node)            => 
            path + "?" + [applyOptions("query"), node].join("=")
        const tagNode   = (node)            => 
            <Link key={[distinct, node].join("-")} className={buttonCls} href={pagePath(node)}>{node}</Link>
        const before    = (index, node, arr) => {
            if (node > 1 && index <= limit) {
                ++index
                --node
                arr.unshift(tagNode(node));
            }

            return [index, node];
        }
        const after     = (index, node, arr) => {
            if (last_page >= node && index <= limit) {
                ++index;
                ++node;
                arr.push(tagNode(node));
            }

            return [index, node];
        }

        for (let i = 1, index = 1, forward = current_page, back = current_page; i < limit; [index, forward] = after(index, forward, arr), [index, back] = before(index, back, arr), i = index != i ? index : i+1);

        return (
            <>
                {arr.map( i => i )}
            </>
        );
    }

    return (
        <div className={applyStyles("outer")}>
            {applyOptions("summary") && summary(rest)}
            <div className={applyStyles("inner")}>
                {boundaries &&
                    <Link className={buttonCls}
                          href={first_page_url}>{applyOptions("first")}</Link>
                }
                {wrappers && 
                    <Link className={buttonCls}
                          href={prev_page_url}
                          disabled={isDisabled(prev_page_url)}>{applyOptions("prev")}</Link>
                }
                {buttons()}
                {wrappers &&
                    <Link className={buttonCls}
                          href={next_page_url}
                          disabled={isDisabled(next_page_url)}>{applyOptions("next")}</Link>
                }
                {boundaries && 
                    <Link className={buttonCls}
                          href={last_page_url}>{applyOptions("last")}</Link>
                }
            </div>
         </div>
    );
}