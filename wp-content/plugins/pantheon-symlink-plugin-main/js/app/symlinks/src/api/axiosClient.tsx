import axios, {AxiosResponse} from "axios";
import {IPluginList, ISymlink, ICreateSymlinkParam, IUntrackedSymlinks} from "../types/SymlinkTypes";

// get the localize variable from WP
declare var pantheon_settings: any;

const axiosClient = axios.create({
    baseURL: `${pantheon_settings.api_url}/pantheon/v1`,
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': pantheon_settings.nonce,
    }
});

export const getPlugins = () =>
    axiosClient.get<IPluginList>('/plugins')
        .then((res) => res.data);

export const getSymlinks = () =>
    axiosClient.get<ISymlink>('/symlink')
        .then((res) => {
            //console.log('Get Symlinks', res.data);
            return res.data;
        });

export const deleteSymlink = ({...data}) => axiosClient.delete('/symlink', {data: data})
    .then((res) => {
        //console.log('Delete Response', res);
    });

export const postSymlink = ({...symlinkData}) =>
    axiosClient.post('/symlink', {...symlinkData})
        .then((res) => {
            //console.log('Response', res);
            return res.data;
        });

export const getUntrackedSymlink = () =>
    axiosClient.get <IUntrackedSymlinks>('/symlink/untracked')
        .then((res) => {
            //console.log('Untracked Symlinks: ', res.data);
            return res.data;
        });

export const postUntrackedSymlink = ({...data}) =>
    axiosClient.post('/symlink/tracked', {...data})
        .then((res) => {
            //console.log('Untracked Posted: ', res);
            return res.data;
        });