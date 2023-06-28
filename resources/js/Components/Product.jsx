import React from 'react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Link } from '@inertiajs/react';

dayjs.extend(relativeTime);

export default function Product({ product }) {

    const {image, name, link, license, wlStatus, attributes, stats, aliases, created_at, updated_at, ...rest} = product;

    return (
        <div className="p-6 flex space-x-2">
            <figure>
                <img  src={image.src}
                      alt={image.alt}
                      width={image.width}
                      height={image.height} />
                <figcaption>{image.alt}</figcaption>
            </figure>
            <header>
                <h3>{name}</h3>
            </header>
            <p><b>Link</b> <Link href={link} className="text-orange-500">Link</Link></p>
            <p><b>License</b> {license}</p>
            <p><b>Status</b> {wlStatus}</p>
            <p><b>Attributes</b> {attributes}</p>
            <p><b>Stats</b> {stats}</p>
            <p><b>Aliases</b> {aliases}</p>
            <p><b>Created</b> {dayjs(created_at).fromNow()}</p>
            <p><b>Updated</b> {dayjs(updated_at).fromNow()}</p>
        </div>
    );
}