import React from 'react';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Link } from '@inertiajs/react';
import Details from './Details';

dayjs.extend(relativeTime);

export default function Product({ product }) {
    const {
        downloads,
        name,
        link,
        license,
        wlStatus,
        attributes,
        stats,
        aliases,
        created_at,
        updated_at,
        ...rest
    } = product;

    const cachedImageUrl = (hash) => ["/file/display", hash].join('/');

    const noImages = (dl) =>
        dl.length === 0
        &&  <figure className="text-center">
                <img alt="Apologies, no image"
                     className="shadow-md rounded-md mx-auto min-h-[344px] min-w-[234px]"/>
                <figcaption>Image hotlink <span className="text-orange-500">none</span></figcaption>
            </figure>

    return (
        <div className="p-2 m-1 shadow-md rounded-lg bg-gray-200 hover:bg-orange-100">
            <header>
                <h3 className="text-lg text-orange-500 text-center">{name}</h3>
            </header>
            {downloads.map((dl) => 
                <figure key={[rest.id, dl.url, 'thumbnail', 'cached'].join('-')}
                        className="text-center">
                    {/* or utilize picture tag/imgsrc attribute with dl.media, which, requires ',' split */}
                    <img src={cachedImageUrl(dl.url)}
                         className={`shadow-md rounded-md mx-auto h-[${dl.height}px] w-[${dl.width}px] object-cover min-h-[344px]`}
                         alt="missing image" />
                    <figcaption>Image hotlink <Link href={dl.hotlink} className="text-orange-500">Here</Link></figcaption>
                </figure>
            )}
            {noImages(downloads)}
            <p className="mt-2"><b>Link</b> <Link href={link} className="text-orange-500">Link</Link></p>
            <p><b>License</b> {license}</p>
            <p><b>Status</b> {wlStatus}</p>
            <Details summary="Attributes" details={attributes} groupId={product.id} isOpen={true} />
            <Details summary="Stats" details={stats} groupId={product.id} />
            <Details summary="Aliases" details={aliases} groupId={product.id} />
            <p><b>Created</b> {dayjs(created_at).fromNow()}</p>
            <p><b>Updated</b> {dayjs(updated_at).fromNow()}</p>
        </div>
    );
}