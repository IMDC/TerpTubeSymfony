define([
    'chai',
    'model/myFilesModel',
    'model/mediaModel'
], function (chai, MyFilesModel, MediaModel) {
    'use strict';

    var expect = chai.expect;

    describe('MyFilesModel', function () {

        var media1;
        var model;

        before(function () {
            media1 = {id: 1};
            model = new MyFilesModel({
                media: [
                    media1,
                    {id: 2},
                    {id: 3}
                ]
            });
        });

        it('should have media array of MediaModel instances', function () {
            model.get('media').forEach(function (element, index, array) {
                expect(element).to.be.an.instanceof(MediaModel);
            });
        });

        it('should not return media', function () {
            var media = model.getMedia(99);
            expect(media).to.be.undefined;
        });

        it('should return media', function () {
            var media = model.getMedia(media1.id);

            expect(media).to.not.be.undefined;
            expect(media).to.be.an.instanceof(MediaModel);
            expect(media.get('id')).to.equal(media1.id);
        });

        after(function () {
            media1 = null;
            model = null;
        });

    });

});
