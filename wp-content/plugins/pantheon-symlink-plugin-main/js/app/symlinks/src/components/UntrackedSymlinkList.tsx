import React, {ReactNode, useState} from "react";
import {Card, Col, Row, Space, Divider, Tag, Tooltip, List, Table, Typography, Empty, ConfigProvider} from "antd";
import * as api from '../api/axiosClient';
import {useMutation, useQuery} from "react-query";
import {IUntrackedSymlinks, UntrackedSymlinkType} from "../types/SymlinkTypes";
import {SmileOutlined} from '@ant-design/icons';
import {ColumnsType} from "antd/es/table";
import {postUntrackedSymlink} from "../api/axiosClient";

declare var pantheon_settings: any;
const {Text} = Typography;

type Item = {
    id: React.Key;
    target?: string;
    link?: string;
    description?: string;
    children?: UntrackedSymlinkType[];
}

const originalData: Item[] = [];
const env = pantheon_settings.environment_check['environment_check'];

const UntrackedSymlinkList = () => {
    const [untrackedData, setUntrackedData] = useState<readonly Item[]>([]);
    const [editableKeys, setEditableRowKeys] = useState<React.Key[]>([]);
    const {data, isLoading} = useQuery('untracked-symlink', api.getUntrackedSymlink, {
        onSuccess: (data) => {
            const untracked: any = data !== undefined && data.untracked.length >= 1 ?
                data.untracked.map((items, i) => {
                    return {
                        id: items.id,
                        target: items.target,
                        link: items.link,
                        description: items.description,
                    }
                }) : [];
            setUntrackedData(untracked);
        }
    });
    const {mutate} = useMutation(postUntrackedSymlink);

    const getPostData = (obj: any) => {
        let postData = {
            'id': obj.id,
            'target': obj.target,
            'link': obj.link,
            'description': obj.description,
        };

        return JSON.stringify(postData);
    }

    const columns: ColumnsType<Item> = [
        {
            'title': 'Target',
            'dataIndex': 'target',
            width: "20%",
        },
        {
            'title': 'Link',
            'dataIndex': 'link',
            width: '20%'
        },
        {
            'title': 'Description',
            'dataIndex': 'description',
            width: '40%'
        },
        (
            /**
             * Enable editing if
             * 1. in DEV environment and Development mode is in SFTP
             * 2. in TEST and LIVE environment
             */
            (env['environment'] !== 'dev' && env['mode'] !== "GIT") || (env['environment'] === 'dev' && env['mode'] === "SFTP") ?
                {
                    'title': 'Action',
                    'dataIndex': 'action',
                    width: 200,
                    render: (_, record) => {
                        return (
                            <a onClick={() => {
                                mutate({untracked: getPostData(record)});
                                setUntrackedData(untrackedData.filter((item) => item.id !== record.id));
                            }}>
                                {pantheon_settings.untracked.add_link}
                            </a>
                        )
                    }
                }
                :
                {
                    'title': 'Action',
                    'dataIndex': 'action',
                    width: 200,
                }
        )
    ];

    return (
        <>
            <Divider orientation="left">{pantheon_settings.divider_title_untracked_symlink}</Divider>
            {untrackedData.length >= 1 ? (
                <>
                    <Space direction={"vertical"} style={{width: "100%"}} size={"middle"}>
                        <div style={{textAlign: "center", maxWidth: "100%"}}>
                            <Text type={"secondary"}>{pantheon_settings.untracked.untracked_info}</Text>
                        </div>
                        <Table
                            columns={columns}
                            loading={isLoading}
                            dataSource={untrackedData}
                            style={{padding: "0 20px 0 20px"}}
                            size={"middle"}
                            pagination={false}
                        />
                    </Space>
                </>
            ) : (
                <>
                    <Space direction={"vertical"} style={{width: "100%"}} size={"middle"}>
                        <div style={{textAlign: "center", maxWidth: "100%"}}>
                            <Text type={"secondary"}>{pantheon_settings.untracked.untracked_info}</Text>
                        </div>
                        <Empty description={<Text type={"secondary"}>No Untracked Symlinks</Text>}></Empty>
                    </Space>
                </>
            )}
        </>
    )
}

export default UntrackedSymlinkList;
