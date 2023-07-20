import React, {ReactNode, useState} from "react";
import * as api from '../api/axiosClient';
import {Card, Col, Row, Space, Divider, Tag, Tooltip, List} from "antd";
import {ThunderboltOutlined, FolderAddOutlined, AppstoreAddOutlined} from "@ant-design/icons";
import {useQuery} from "react-query";
import Meta from "antd/es/card/Meta";

declare var pantheon_settings: any;

const PluginLists = () => {
    const [listPlugins, setListPlugins] = useState([]);
    const {data, isLoading} = useQuery('plugin-list', api.getPlugins, {
        onSuccess: (data) => {
            const pluginList: any = data !== undefined && data.plugins.length >= 1 ?
                data.plugins.map((row: any, i: number) => {
                    return {
                        key: row.plugin_name,
                        plugin_name: row.plugin_name,
                        is_active: row.is_active,
                        is_installed: row.is_installed,
                        img_src: row.img_src,
                    }
                }) : [];

            setListPlugins(pluginList);
        }
    });

    type IconButtonProps = {
        text?: string;
        icon?: ReactNode;
        cb?: () => void;
        tooltipTitle?: string;

    }

    const IconButton = ({text, icon, cb, tooltipTitle}: IconButtonProps) => {
        return (
            <div onClick={cb}>
                <Tooltip placement="top" title={tooltipTitle}>
                    {icon}
                    {text}
                </Tooltip>
            </div>
        );
    }

    const installPluginLink = (keyword?: any) => {
        let url = `${pantheon_settings.admin_url}/plugin-install.php?s=${keyword}&tab=search&type=term`
        window.open(url, '_blank');
    }

    return (
        <>
            <Divider orientation="left">{pantheon_settings.divider_title_default_plugin}</Divider>
            <List
                style={{padding: "0 20px 0 20px"}}
                grid={{gutter: 16, column: 6}}
                dataSource={listPlugins}
                loading={isLoading}
                renderItem={(item, index) => (
                    <List.Item
                        style={{opacity: item['is_installed'] && item['is_active'] ? "100%" : "20%"}}
                    >
                        <List.Item.Meta
                            avatar={<img src={item['img_src']} style={{width: "26px"}}></img>}
                            title={item['plugin_name']}
                        />
                        <Space size={[0, 8]} wrap>
                            {item['is_installed'] ? <Tag color="success">Installed</Tag> : ''}
                            {item['is_active'] ? <Tag color="success">Activated</Tag> : ''}
                        </Space>
                    </List.Item>
                )}
            />
        </>
    );
}

export default PluginLists;