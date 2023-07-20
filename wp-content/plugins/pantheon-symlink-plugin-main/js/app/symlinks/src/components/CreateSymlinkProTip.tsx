import {ThunderboltOutlined} from "@ant-design/icons";
import {Alert, Divider} from "antd";
import * as React from "react";

declare var pantheon_settings: any;

export const CreateSymlinkProTip = () => {
    return (
        <>
            <Alert
                message={pantheon_settings.symlinks.protip_title}
                description={
                    <>
                        <p>{pantheon_settings.symlinks.symlink_doc} <a href={pantheon_settings.symlinks.link}
                                                                       target="_blank">here.</a>
                        </p>
                        <ul>
                            <li><strong>Target : </strong>{pantheon_settings.symlinks.target_description}</li>
                            <li><strong>Link : </strong>{pantheon_settings.symlinks.link_description}</li>
                        </ul>
                    </>
                }
                type="info"
                icon={<ThunderboltOutlined/>}
                showIcon
                style={{borderRadius: 0, marginBottom: 30, marginTop: 30}}
            />
        </>
    );
}