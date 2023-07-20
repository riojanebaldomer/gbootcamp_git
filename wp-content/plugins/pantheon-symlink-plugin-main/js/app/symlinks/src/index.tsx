import React from 'react';
import {ConfigProvider} from "antd";
import En_US from 'antd/locale/en_US';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import {QueryClient, QueryClientProvider} from 'react-query';

const queryClient = new QueryClient({});

const root = ReactDOM.createRoot(
    document.getElementById('pantheon_admin_app') as HTMLElement
);

root.render(
    <React.StrictMode>
        <QueryClientProvider client={queryClient}>
            <ConfigProvider locale={En_US}>
                <App/>
            </ConfigProvider>
        </QueryClientProvider>
    </React.StrictMode>
);
