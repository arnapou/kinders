// import React, { Component } from 'react';
// import logo from './logo.svg';
// import './App.css';
//
// class App extends Component {
//   render() {
//     return (
//       <div className="App">
//         <header className="App-header">
//           <img src={logo} className="App-logo" alt="logo" />
//           <p>
//             Edit <code>src/App.js</code> and save to reload.
//           </p>
//           <a
//             className="App-link"
//             href="https://reactjs.org"
//             target="_blank"
//             rel="noopener noreferrer"
//           >
//             Learn React
//           </a>
//         </header>
//       </div>
//     );
//   }
// }
//
// export default App;

// import React, { Component } from 'react';
// import { HydraAdmin } from '@api-platform/admin';
//
// class App extends Component {
//     render() {
//         return <HydraAdmin entrypoint="http://kinders.arnapou.local/api/"/> // Replace with your own API entrypoint
//     }
// }
//
// export default App;

import React from 'react';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { HydraAdmin, hydraClient, fetchHydra as baseFetchHydra } from '@api-platform/admin';
import authProvider from './authProvider';
import { Redirect } from 'react-router-dom';

const apiWebsite = 'http://kinders.arnapou.local';
const entrypoint = apiWebsite + '/api'; // Change this by your own entrypoint
const fetchHeaders = {'Authorization': `Bearer ${window.localStorage.getItem('token')}`};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders),
});
const dataProvider = api => hydraClient(api, fetchHydra);
const apiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint, { headers: new Headers(fetchHeaders) })
    .then(
        ({ api }) => ({ api }),
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