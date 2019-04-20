import React from 'react';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { HydraAdmin, hydraClient, fetchHydra as baseFetchHydra } from '@api-platform/admin';
import { FunctionField, ImageField, ImageInput, RichTextField } from 'react-admin';
// import RichTextInput from 'ra-input-rich-text';
import authProvider from './authProvider';
import { Redirect } from 'react-router-dom';

const entrypoint = window.env.API_BASE_URL;
const fetchHeaders = {'Authorization': `Bearer ${window.localStorage.getItem('token')}`};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders),
});
const dataProvider = api => hydraClient(api, fetchHydra);
const apiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint, { headers: new Headers(fetchHeaders) })
    .then(
        ({ api }) => ({ api }),
        // ({ api }) => {
        //
        //     // const books = api.resources.find(({ name }) => 'books' === name);
        //     // const description = books.fields.find(({ name }) => 'description' === name);
        //     //
        //     // description.input = props => (
        //     //     <RichTextInput {...props} source="description" />
        //     // );
        //     //
        //     // description.input.defaultProps = {
        //     //     addField: true,
        //     //     addLabel: true,
        //     // };
        //
        //     api.resources.map(resource => {
        //         // if ('http://schema.org/ImageObject' === resource.id) {
        //         if ('http://schema.org/image' === resource.id) {
        //             resource.fields.map(field => {
        //                 if ('http://schema.org/contentUrl' === field.id) {
        //                     field.denormalizeData = value => ({
        //                         src: value
        //                     });
        //
        //                     field.field = props => (
        //                         <ImageField {...props} source={`${field.name}.src`} />
        //                     );
        //
        //                     field.input = (
        //                         <ImageInput accept="image/*" key={field.name} multiple={false} source={field.name}>
        //                             <ImageField source="src"/>
        //                         </ImageInput>
        //                     );
        //
        //                     field.normalizeData = value => {
        //                         if (value && value.rawFile instanceof File) {
        //                             const body = new FormData();
        //                             body.append('file', value.rawFile);
        //
        //                             return fetch(`${entrypoint}/images/upload`, { body, method: 'POST' })
        //                                 .then(response => response.json());
        //                         }
        //
        //                         return value.src;
        //                     };
        //                 }
        //
        //                 return field;
        //             });
        //         }
        //
        //         return resource;
        //     });
        //
        //     return { api };
        // },
        (result) => {
            switch (result.status) {
                case 401:
                    return Promise.resolve({
                        api: result.api,
                        customRoutes: [{
                            props: {
                                path: '/',
                                render: () => <Redirect to={`/login`}/>,
                            },
                        }],
                    });

                default:
                    return Promise.reject(result);
            }
        },
    );

export default props => (
    <HydraAdmin
apiDocumentationParser={apiDocumentationParser}
authProvider={authProvider}
entrypoint={entrypoint}
dataProvider={dataProvider}
/>
);