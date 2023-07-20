import React from 'react';
import './App.css';
import {Typography, Row, Col, Card, Space, Alert, Result, Button, Tag} from 'antd';
import PluginLists from './components/PluginList';
import EditableTable from "./components/CreateSymlinkTable/EditableTable";
import UntrackedSymlinkList from "./components/UntrackedSymlinkList";
import {CreateSymlinkProTip} from "./components/CreateSymlinkProTip";


const {Title} = Typography;
declare var pantheon_settings: any;

const columns = [
    {
        title: ''
    }
]

function App() {
    return (
        <div className="pantheon-symlinks">
            <Space direction='vertical' size='middle' style={{display: 'flex'}}>
                <Title level={2} style={{marginTop: "10px"}}>
                    {pantheon_settings.title} {pantheon_settings.environment_check['environment_check']['status'] ?
                    <Tag color={"success"}>{pantheon_settings.environment_check['environment_check']['error']}</Tag> :
                    <Tag color={"error"}>{pantheon_settings.environment_check['environment_check']['error']}</Tag>}
                </Title>
                <Row>
                    <Col span={24}>
                        <Card bordered={true} style={{borderRadius: "0px", border: "1px"}}>
                            <PluginLists/>
                            <EditableTable/>
                            <UntrackedSymlinkList/>
                            <CreateSymlinkProTip/>
                        </Card>
                    </Col>
                </Row>
            </Space>
        </div>
    );
}

export default App;
