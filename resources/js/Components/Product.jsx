import React from 'react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Link } from '@inertiajs/react';

dayjs.extend(relativeTime);

export default function Product({ product }) {

    const {thumbnails, name, link, license, wlStatus, attributes, stats, aliases, created_at, updated_at, ...rest} = product;
    // const {age, gender, ethnicity, hairColor, piercings, breestSize, breastType, orientation, ...attrRest} = attributes;

    return (
        <div className="p-6 space-x-2">
            <figure>
                <img  src={thumbnails[0].url}
                      alt={thumbnails[0].url}
                      width={thumbnails[0].width}
                      height={thumbnails[0].height} />
                <figcaption>Original link <Link href={thumbnails[0].url} className="text-orange-500">Here</Link></figcaption>
            </figure>
            <header>
                <h3>{name}</h3>
            </header>
            <p><b>Link</b> <Link href={link} className="text-orange-500">Link</Link></p>
            <p><b>License</b> {license}</p>
            <p><b>Status</b> {wlStatus}</p>
            <ul>
                <li className="head"><b>Attributes</b></li>
                {Object.keys(attributes).map((keyname, index) => <li key={[keyname, product.id].join("-")}>{keyname}: {attributes[keyname]}</li>)}
            </ul>
            <ul>
                <li className="head"><b>Stats</b></li>
                {Object.keys(stats).map((keyname, index) => <li key={[keyname, product.id].join("-")}>{keyname}: {stats[keyname]}</li>)}
            </ul>
            <ul>
                <li className="head"><b>Aliases</b></li>
                {aliases.map(v => <li key={[v, product.id].join("-")}>{v}</li>)}
            </ul>
            <p><b>Created</b> {dayjs(created_at).fromNow()}</p>
            <p><b>Updated</b> {dayjs(updated_at).fromNow()}</p>
        </div>
    );
}