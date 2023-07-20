import React, {useState} from "react";
import type {ProColumns} from '@ant-design/pro-components';
import {
    EditableProTable,
    ProCard,
    ProFormField,
} from '@ant-design/pro-components';
import {useQuery, useMutation} from "react-query";
import {getSymlinks, postSymlink, deleteSymlink} from "../../api/axiosClient";
import {ISymlink, SymlinkType} from "../../types/SymlinkTypes";
import {Divider, Input, Popconfirm, Alert, Space} from "antd";
import {CreateSymlinkProTip} from "../CreateSymlinkProTip";

declare var pantheon_settings: any;

type Item = {
    id: React.Key;
    target?: string;
    link?: string;
    description?: string;
    children?: SymlinkType[];
};

const originalData: Item[] = [];
const env = pantheon_settings.environment_check['environment_check'];
console.log(env);
const EditableTable = () => {
    const [showAlertMessage, setShowAlertMessage] = useState(false);
    const [editableKeys, setEditableRowKeys] = useState<React.Key[]>([]);
    const [dataSource, setDataSource] = useState<readonly Item[]>([]);
    const [isNew, setIsNew] = useState(true);
    const {data: symlinkData, isLoading} = useQuery('symlinks', getSymlinks, {
        onSuccess: (symlinkData) => {
            const symlink: any = symlinkData !== undefined && symlinkData.symlinks.length >= 1 ?
                symlinkData.symlinks.map((row: any, i) => {
                    return {
                        id: row.id,
                        target: row.target,
                        link: row.link,
                        description: row.description,
                    }
                }) : [];

            setDataSource(symlink);
        }
    });
    const {mutate} = useMutation(postSymlink);
    const removeSymlink = useMutation(deleteSymlink);

    // Get new random Id
    const getNewId = () => {
        const randNumber = Math.floor(Math.random() * (9999 - 1 + 1) + 1);
        return randNumber.toFixed(0);
    }

    // Change symlink
    const isSymlinkChange = (newRowData: any, currentRowData: any) => {
        if (newRowData['target'] !== currentRowData['target'] || newRowData['link'] !== currentRowData['link']) {
            return true;
        }

        return false;
    }

    // get status of the symlink to be created
    // created - if in dev environment
    // draft - if created under test or live
    const get_symlink_stattus = () => {
        if (env['environment'] !== 'dev' && env['environment'] !== 'local') {
            return 'draft';
        } else {
            return 'created';
        }

    }

    // Get selected data in object
    const getPostData = (obj: any, isNew: boolean) => {
        let postData = {
            'id': obj.id,
            'target': obj.target,
            'link': obj.link,
            'description': obj.description,
            'status': get_symlink_stattus(),
            'is_new': isNew,
        };

        return JSON.stringify(postData);
    }

    // Set column properties
    const columns: ProColumns<Item>[] = [
        {
            title: 'Target',
            dataIndex: 'target',
            tooltip: pantheon_settings.columns.header.info_target,
            renderFormItem: () => <Input placeholder='eg: ./uploads/cache'/>,
            formItemProps: (form, {rowIndex}) => {
                return {
                    rules:
                        rowIndex > 1 ? [{required: true, message: pantheon_settings.form.input.target_required}] : [],
                };
            },

            width: '20%',
        },
        {
            title: 'Link',
            dataIndex: 'link',
            tooltip: pantheon_settings.columns.header.info_link,
            renderFormItem: () => <Input placeholder="eg: /wp-content/cache"/>,
            formItemProps: (form, {rowIndex}) => {
                return {
                    rules:
                        rowIndex > 1 ? [{required: true, message: pantheon_settings.form.input.link_required}] : [],
                };
            },
            width: '20%',
        },
        {
            title: 'Description',
            dataIndex: 'description',
            renderFormItem: () => <Input placeholder="Description of your symlink, or plugin name."/>,
            formItemProps: (form, {rowIndex}) => {
                return {
                    rules:
                        rowIndex > 1 ? [{required: true, message: pantheon_settings.form.input.desc_required}] : [],
                };
            },
            width: '40%',
        },
        {
            title: 'Status',
            dataIndex: 'status',
            width: '10%',
            readonly: true,
            valueType: 'select',
            valueEnum: {
                created: {
                    text: 'Created',
                    status: 'Success'
                },
                draft: {
                    text: 'Draft',
                    status: 'Warning'
                },
                failed: {
                    text: 'Failed',
                    status: 'Error'
                }
            }
        },
        (
            /**
             * Enable editing if
             * 1. in DEV environment and Development mode is in SFTP
             * 2. in TEST and LIVE environment
             */
            (env['environment'] !== 'dev' && env['mode'] !== "GIT") || (env['environment'] === 'dev' && env['mode'] === "SFTP") ?
                {
                    title: 'Action',
                    valueType: 'option',
                    width: 200,
                    render: (text, record, _, action) => {
                        return (
                            <>
                                <Space direction={"horizontal"} size={"middle"}>
                                    <a
                                        key="editable"
                                        onClick={() => {
                                            action?.startEditable?.(record.id);
                                            setIsNew(false);
                                            setShowAlertMessage(true);
                                        }}
                                    >
                                        Edit
                                    </a>
                                    <Popconfirm
                                        title="Delete the symlink"
                                        description="Are you sure to delete?"
                                        okText="Yes"
                                        cancelText="No"
                                        onConfirm={() => {
                                            setShowAlertMessage(false);
                                            setDataSource(dataSource.filter((item) => item.id !== record.id));
                                            console.log('Delete: ', record);
                                            removeSymlink.mutate({id: record.id});
                                        }}
                                    >
                                        <a key="delete"> Delete </a>
                                    </Popconfirm>
                                </Space>
                            </>
                        )
                    }
                }
                :
                {
                    title: 'Action',
                    valueType: 'option',
                    width: 200,
                }
        )
    ];

    return (
        <>
            <Divider orientation="left">{pantheon_settings.divider_title_create_symlink}</Divider>
            <Space direction={"vertical"} size={"large"}>
                {showAlertMessage && (
                    <Alert
                        type={"warning"}
                        showIcon
                        message={"Warning"}
                        description={"Changing symlink Target and Link details will remove the existing symlink and create a new one based on your changes."}
                        style={{borderRadius: 0}}
                    />
                )}
                <EditableProTable<Item>
                    rowKey="id"
                    recordCreatorProps={
                        /**
                         * Enable editing if
                         * 1. in DEV environment and Development mode is in SFTP
                         * 2. in TEST and LIVE environment
                         */
                        (env['environment'] !== 'dev' && env['mode'] == "") || (env['environment'] === 'dev' && env['mode'] === 'SFTP' && env['status'] === true)
                            ?
                            {
                                lang: 'en',
                                position: 'bottom',
                                record: () => ({id: getNewId()}),
                                creatorButtonText: 'Add New Symlink',
                            }
                            : false
                    }
                    loading={isLoading}
                    columns={columns}
                    request={async () => ({
                        data: originalData,
                        total: 3,
                        success: true,
                    })}
                    value={dataSource}
                    onChange={setDataSource}
                    editable={{
                        type: 'multiple',
                        editableKeys,
                        onSave: async (rowKey, data, row) => {
                            mutate({symlinks: getPostData(data, isNew)});
                            setIsNew(true);
                            setShowAlertMessage(false);
                        },
                        onChange: setEditableRowKeys,
                        onCancel: async () => {
                            setShowAlertMessage(false);
                        },
                        onlyAddOneLineAlertMessage: 'Only one line can be added.',
                    }}
                />
            </Space>
        </>
    )
}

export default EditableTable;