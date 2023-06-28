export default function Image({data}) {
    const {source, examined, downloaded, cached, filename, md5, ...rest} = data;
    const md5AsFile = ["/file", "display", md5].join('/');

    return (
        <div className="text-gray-500">
            <div className="mx-auto p-4 flex items-center justify-between">
                <figure>
                    <img  src={source}
                          alt={source}
                          width="100" />
                    <figcaption>Hotlinked</figcaption>
                </figure>
                <figure>
                    <img  src={filename}
                          alt={filename}
                          width="100" />
                    <figcaption>Downloaded</figcaption>
                </figure>
                <figure>
                    <img  src={md5AsFile}
                          alt={md5}
                          width="100" />
                    <figcaption>Cached</figcaption>
                </figure>
            </div>
            <ul>
                <li><b>Source</b>: {source}</li>
                <li><b>Examined</b>: {examined === true ? "true" : "false"}</li>
                <li><b>Downloaded</b>: {downloaded === true ? "true" : "false"}</li>
                <li><b>Cached</b>: {cached === true ? "true" : "false"}</li>
            </ul>
        </div>
    );
};
